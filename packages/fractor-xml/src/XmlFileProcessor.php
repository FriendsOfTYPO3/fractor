<?php

declare(strict_types=1);

namespace a9f\FractorXml;

use a9f\Fractor\Application\Contract\FileProcessor;
use a9f\Fractor\Application\ValueObject\File;
use a9f\Fractor\Caching\Detector\ChangedFilesDetector;
use a9f\Fractor\Exception\ShouldNotHappenException;
use a9f\Fractor\ValueObject\Indent;
use a9f\FractorXml\Contract\DomNodeVisitor;
use a9f\FractorXml\Contract\Formatter;
use a9f\FractorXml\Contract\XmlFractor;
use a9f\FractorXml\Printer\SourceMap;
use a9f\FractorXml\Printer\XmlPrinter;
use a9f\FractorXml\ValueObject\XmlFormatConfiguration;
use a9f\FractorXml\ValueObjectFactory\DomDocumentFactory;

/**
 * @implements FileProcessor<XmlFractor>
 */
final readonly class XmlFileProcessor implements FileProcessor
{
    /**
     * @param iterable<XmlFractor> $rules
     */
    public function __construct(
        private DomDocumentFactory $domDocumentFactory,
        private Formatter $formatter,
        private XmlPrinter $xmlPrinter,
        private iterable $rules,
        private Indent $indent,
        private ChangedFilesDetector $changedFilesDetector,
        private XmlFormatConfiguration $xmlFormatConfiguration
    ) {
    }

    public function canHandle(File $file): bool
    {
        return in_array($file->getFileExtension(), $this->allowedFileExtensions(), true);
    }

    /**
     * @param list<DomNodeVisitor> $appliedRules
     */
    public function handle(File $file, iterable $appliedRules): void
    {
        $fileHasChanged = \false;
        $originalXml = $file->getOriginalContent();
        $document = $this->domDocumentFactory->createFromXml($originalXml);
        $sourceMap = SourceMap::create($originalXml, $document);

        // Printed before the rules as well, so the diff shows only the rule's change
        $oldXml = $this->print($document, $sourceMap);
        $file->changeOriginalContent($oldXml);

        $iterator = new DomDocumentIterator($appliedRules);
        $iterator->traverseDocument($file, $document);

        $newXml = $this->print($document, $sourceMap);

        if ($newXml === $originalXml) {
            $this->changedFilesDetector->addCachableFile($file->getFilePath());
            return;
        }

        $file->changeFileContent($newXml);
        if ($file->hasChanged()) {
            $fileHasChanged = \true;
        }
        if (! $fileHasChanged) {
            $this->changedFilesDetector->addCachableFile($file->getFilePath());
        }
    }

    /**
     * @return list<non-empty-string>
     */
    public function allowedFileExtensions(): array
    {
        return $this->xmlFormatConfiguration->allowedFileExtensions;
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

        $xml = str_replace('-&gt;', '->', $this->saveXml($document));

        return rtrim($this->formatter->format($this->indent, $xml)) . "\n";
    }

    private function saveXml(\DOMDocument $document): string
    {
        $xml = $document->saveXML();
        if ($xml === false) {
            throw new ShouldNotHappenException('New file content should be a string');
        }

        return $xml;
    }
}
