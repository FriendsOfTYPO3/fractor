<?php

declare(strict_types=1);

namespace a9f\FractorXliff;

use a9f\Fractor\Application\Contract\FileProcessor;
use a9f\Fractor\Application\ValueObject\AppliedRule;
use a9f\Fractor\Application\ValueObject\File;
use a9f\Fractor\Caching\Detector\ChangedFilesDetector;
use a9f\Fractor\Exception\ShouldNotHappenException;
use a9f\Fractor\ValueObject\Indent;
use a9f\FractorXliff\Contract\XliffFractorRule;
use a9f\FractorXliff\ValueObject\XliffDocument;
use a9f\FractorXliff\ValueObject\XliffFormatConfiguration;
use a9f\FractorXliff\ValueObject\XliffVersion;
use PrettyXml\Formatter;

/**
 * @implements FileProcessor<XliffFractorRule>
 */
final readonly class XliffFileProcessor implements FileProcessor
{
    /**
     * @param iterable<XliffFractorRule> $rules
     */
    public function __construct(
        private DomDocumentFactory $domDocumentFactory,
        private Formatter $formatter,
        private iterable $rules,
        private Indent $indent,
        private ChangedFilesDetector $changedFilesDetector,
        private XliffFormatConfiguration $xliffFormatConfiguration
    ) {
    }

    public function canHandle(File $file): bool
    {
        return in_array($file->getFileExtension(), $this->allowedFileExtensions(), true);
    }

    /**
     * @param iterable<XliffFractorRule> $appliedRules
     */
    public function handle(File $file, iterable $appliedRules): void
    {
        $originalXml = $file->getOriginalContent();
        $document = $this->domDocumentFactory->createFromXml($originalXml);

        // Normalize baseline formatting for clean diffs
        $oldXml = $this->saveXml($document);
        $oldXml = $this->formatXml($oldXml);
        $file->changeOriginalContent($oldXml);

        $version = XliffVersion::fromDomDocument($document);
        $xliffDocument = new XliffDocument($document, $version, $file);

        foreach ($appliedRules as $rule) {
            $result = $rule->refactor($xliffDocument);
            if ($result !== null) {
                $xliffDocument = $result;
                $file->addAppliedRule(AppliedRule::fromRule($rule));
            }
        }

        $newXml = $this->saveXml($xliffDocument->document);
        $newXml = $this->formatXml($newXml);

        // Compare against raw original to detect formatting changes too
        if ($newXml === $originalXml) {
            return;
        }

        $file->changeFileContent($newXml);
        if (! $file->hasChanged()) {
            $this->changedFilesDetector->addCachableFile($file->getFilePath());
        }
    }

    /**
     * @return list<non-empty-string>
     */
    public function allowedFileExtensions(): array
    {
        return $this->xliffFormatConfiguration->allowedFileExtensions;
    }

    public function getAllRules(): iterable
    {
        return $this->rules;
    }

    private function saveXml(\DOMDocument $document): string
    {
        // Ensure a proper encoding so non-ASCII characters stay literal
        if ($document->encoding === null || $document->encoding === '') {
            $document->encoding = 'UTF-8';
        }
        $xml = $document->saveXML();
        if ($xml === false) {
            throw new ShouldNotHappenException('Could not save XLIFF document');
        }

        return $xml;
    }

    private function formatXml(string $xml): string
    {
        $indentCharacter = $this->indent->isSpace() ? Indent::CHARACTERS[Indent::STYLE_SPACE] : Indent::CHARACTERS[Indent::STYLE_TAB];
        $this->formatter->setIndentCharacter($indentCharacter);
        $this->formatter->setIndentSize($this->indent->length());

        return rtrim($this->formatter->format($xml)) . "\n";
    }
}
