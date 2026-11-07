<?php

declare(strict_types=1);

namespace a9f\FractorXliff\Transform;

use a9f\Fractor\Contract\NoChangelogRequired;
use a9f\Fractor\RuleDoc\CodeSample\CodeSample;
use a9f\Fractor\RuleDoc\RuleDefinition;
use a9f\FractorXliff\Contract\XliffFractorRule;
use a9f\FractorXliff\ValueObject\XliffDocument;
use a9f\FractorXliff\ValueObject\XliffVersion;

/**
 * @see \a9f\FractorXliff\Tests\Transform\ConvertXliff1To2Fractor\ConvertXliff1To2FractorTest
 */
final class ConvertXliff1To2Fractor implements XliffFractorRule, NoChangelogRequired
{
    private const XLIFF_2_NAMESPACE = 'urn:oasis:names:tc:xliff:document:2.0';

    public function refactor(XliffDocument $xliffDocument): ?XliffDocument
    {
        if ($xliffDocument->version === XliffVersion::V2_0) {
            return null;
        }

        $oldDoc = $xliffDocument->document;
        $newDoc = new \DOMDocument('1.0', 'UTF-8');
        $newDoc->formatOutput = true;

        $oldRoot = $oldDoc->documentElement;
        if (! $oldRoot instanceof \DOMElement) {
            return null;
        }

        $newRoot = $newDoc->createElementNS(self::XLIFF_2_NAMESPACE, 'xliff');
        $newRoot->setAttribute('version', '2.0');
        $newDoc->appendChild($newRoot);

        $this->convertFiles($oldRoot, $newDoc, $newRoot);

        return new XliffDocument($newDoc, XliffVersion::V2_0, $xliffDocument->file);
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Convert XLIFF 1.2 files to XLIFF 2.0 format (note: within TYPO3, XLIFF 2.0 is only supported from v14 onwards)',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
<?xml version="1.0" encoding="UTF-8"?>
<xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2">
    <file source-language="en" datatype="plaintext" original="messages">
        <header/>
        <body>
            <trans-unit id="label.hello">
                <source>Hello</source>
            </trans-unit>
        </body>
    </file>
</xliff>
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
<?xml version="1.0" encoding="UTF-8"?>
<xliff version="2.0" xmlns="urn:oasis:names:tc:xliff:document:2.0" srcLang="en">
    <file id="messages">
        <unit id="label.hello">
            <segment>
                <source>Hello</source>
            </segment>
        </unit>
    </file>
</xliff>
CODE_SAMPLE
                ),

            ]
        );
    }

    private function convertFiles(\DOMElement $oldRoot, \DOMDocument $newDoc, \DOMElement $newRoot): void
    {
        foreach ($this->getChildElementsByTagName($oldRoot, 'file') as $oldFile) {
            $sourceLang = $oldFile->getAttribute('source-language');
            $targetLang = $oldFile->getAttribute('target-language');

            if ($sourceLang !== '') {
                $newRoot->setAttribute('srcLang', $sourceLang);
            }

            if ($targetLang !== '') {
                $newRoot->setAttribute('trgLang', $targetLang);
            }

            $fileId = $oldFile->getAttribute('original');
            if ($fileId === '') {
                $fileId = 'f1';
            }

            $newFile = $newDoc->createElementNS(self::XLIFF_2_NAMESPACE, 'file');
            $newFile->setAttribute('id', $fileId);
            $newRoot->appendChild($newFile);

            $body = $this->getFirstChildElementByTagName($oldFile, 'body');
            if ($body instanceof \DOMElement) {
                $this->convertChildren($body, $newDoc, $newFile);
            }
        }
    }

    private function convertChildren(\DOMElement $parent, \DOMDocument $newDoc, \DOMElement $newParent): void
    {
        foreach ($parent->childNodes as $child) {
            if (! $child instanceof \DOMElement) {
                continue;
            }

            $localName = $child->localName ?? '';
            if ($localName === 'trans-unit') {
                $this->convertTransUnit($child, $newDoc, $newParent);
            } elseif ($localName === 'group') {
                $this->convertGroup($child, $newDoc, $newParent);
            }
        }
    }

    private function convertTransUnit(\DOMElement $transUnit, \DOMDocument $newDoc, \DOMElement $newParent): void
    {
        $unit = $newDoc->createElementNS(self::XLIFF_2_NAMESPACE, 'unit');
        $id = $transUnit->getAttribute('id');
        if ($id !== '') {
            $unit->setAttribute('id', $id);
        }

        $newParent->appendChild($unit);

        $this->convertNotes($transUnit, $newDoc, $unit);

        $segment = $newDoc->createElementNS(self::XLIFF_2_NAMESPACE, 'segment');

        $approved = $transUnit->getAttribute('approved');
        if ($approved === 'yes') {
            $segment->setAttribute('state', 'final');
        } elseif ($approved === 'no') {
            $segment->setAttribute('state', 'translated');
        }

        $source = $this->getFirstChildElementByTagName($transUnit, 'source');
        if ($source instanceof \DOMElement) {
            $newSource = $newDoc->createElementNS(self::XLIFF_2_NAMESPACE, 'source');
            $this->copyInnerContent($source, $newDoc, $newSource);
            $segment->appendChild($newSource);
        }

        $target = $this->getFirstChildElementByTagName($transUnit, 'target');
        if ($target instanceof \DOMElement) {
            $newTarget = $newDoc->createElementNS(self::XLIFF_2_NAMESPACE, 'target');
            $this->copyInnerContent($target, $newDoc, $newTarget);
            $segment->appendChild($newTarget);
        }

        $unit->appendChild($segment);
    }

    private function convertGroup(\DOMElement $oldGroup, \DOMDocument $newDoc, \DOMElement $newParent): void
    {
        $newGroup = $newDoc->createElementNS(self::XLIFF_2_NAMESPACE, 'group');
        $id = $oldGroup->getAttribute('id');
        if ($id !== '') {
            $newGroup->setAttribute('id', $id);
        }

        $newParent->appendChild($newGroup);
        $this->convertChildren($oldGroup, $newDoc, $newGroup);
    }

    private function convertNotes(\DOMElement $transUnit, \DOMDocument $newDoc, \DOMElement $unit): void
    {
        $noteElements = $this->getChildElementsByTagName($transUnit, 'note');
        if ($noteElements === []) {
            return;
        }

        $notesContainer = $newDoc->createElementNS(self::XLIFF_2_NAMESPACE, 'notes');
        foreach ($noteElements as $oldNote) {
            $newNote = $newDoc->createElementNS(self::XLIFF_2_NAMESPACE, 'note');
            $this->copyInnerContent($oldNote, $newDoc, $newNote);
            $notesContainer->appendChild($newNote);
        }

        $unit->appendChild($notesContainer);
    }

    private function copyInnerContent(\DOMElement $source, \DOMDocument $newDoc, \DOMElement $target): void
    {
        foreach ($source->childNodes as $child) {
            $imported = $newDoc->importNode($child, true);
            $target->appendChild($imported);
        }
    }

    /**
     * @return list<\DOMElement>
     */
    private function getChildElementsByTagName(\DOMElement $parent, string $tagName): array
    {
        $elements = [];
        foreach ($parent->childNodes as $child) {
            if ($child instanceof \DOMElement && ($child->localName ?? '') === $tagName) {
                $elements[] = $child;
            }
        }

        return $elements;
    }

    private function getFirstChildElementByTagName(\DOMElement $parent, string $tagName): ?\DOMElement
    {
        foreach ($parent->childNodes as $child) {
            if ($child instanceof \DOMElement && ($child->localName ?? '') === $tagName) {
                return $child;
            }
        }

        return null;
    }
}
