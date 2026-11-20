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
use a9f\FractorXml\Printer\SourceMap;
use a9f\FractorXml\Printer\XmlPrinter;
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
        private XmlPrinter $xmlPrinter,
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
        $sourceMap = SourceMap::create($originalXml, $document);

        // Printed before the rules as well, so the diff shows only the rule's change
        $oldXml = $this->print($document, $sourceMap);
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

        $newXml = $this->print($xliffDocument->document, $sourceMap);

        // Compare against raw original to detect formatting changes too
        if ($newXml === $originalXml) {
            $this->changedFilesDetector->addCachableFile($file->getFilePath());
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

    private function print(\DOMDocument $document, ?SourceMap $sourceMap): string
    {
        if ($this->xmlPrinter->canPrint($document)) {
            return $this->xmlPrinter->print($document, $this->indent, $sourceMap);
        }

        return $this->formatXml($this->saveXml($document));
    }

    private function saveXml(\DOMDocument $document): string
    {
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
