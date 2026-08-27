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

        // This is a hacky trick to keep format and create a nice diff later
        $oldXml = $this->saveXml($document);
        $oldXml = str_replace('-&gt;', '->', $oldXml);
        $oldXml = rtrim($this->formatter->format($this->indent, $oldXml)) . "\n";
        $file->changeOriginalContent($oldXml);

        $iterator = new DomDocumentIterator($appliedRules);
        $iterator->traverseDocument($file, $document);

        $newXml = $this->saveXml($document);
        $newXml = str_replace('-&gt;', '->', $newXml);
        $newXml = rtrim($this->formatter->format($this->indent, $newXml)) . "\n";

        if ($newXml === $originalXml) {
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

    private function saveXml(\DOMDocument $document): string
    {
        $xml = $document->saveXML();
        if ($xml === false) {
            throw new ShouldNotHappenException('New file content should be a string');
        }

        return $xml;
    }
}
