<?php

declare(strict_types=1);

namespace a9f\FractorXliff\CodeQuality;

use a9f\Fractor\Contract\NoChangelogRequired;
use a9f\Fractor\RuleDoc\CodeSample\CodeSample;
use a9f\Fractor\RuleDoc\RuleDefinition;
use a9f\FractorXliff\Contract\XliffFractorRule;
use a9f\FractorXliff\ValueObject\XliffDocument;
use a9f\FractorXliff\ValueObject\XliffVersion;

/**
 * @see \a9f\FractorXliff\Tests\CodeQuality\EnsureXliffHasSourceLanguageFractor\EnsureXliffHasSourceLanguageFractorTest
 */
final class EnsureXliffHasSourceLanguageFractor implements XliffFractorRule, NoChangelogRequired
{
    private const DEFAULT_SOURCE_LANGUAGE = 'en';

    public function refactor(XliffDocument $xliffDocument): ?XliffDocument
    {
        if ($xliffDocument->version === XliffVersion::V2_0) {
            return $this->ensureV2SourceLanguage($xliffDocument);
        }

        return $this->ensureV1SourceLanguage($xliffDocument);
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Ensure XLIFF files have the required source-language (v1.x) or srcLang (v2.0) attribute',
            [new CodeSample(
                <<<'CODE_SAMPLE'
<?xml version="1.0" encoding="UTF-8"?>
<xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2">
    <file datatype="plaintext" original="messages">
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
<xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2">
    <file source-language="en" datatype="plaintext" original="messages">
        <body>
            <trans-unit id="label.hello">
                <source>Hello</source>
            </trans-unit>
        </body>
    </file>
</xliff>
CODE_SAMPLE
            )]
        );
    }

    private function ensureV1SourceLanguage(XliffDocument $xliffDocument): ?XliffDocument
    {
        $changed = false;
        $rootElement = $xliffDocument->document->documentElement;
        if (! $rootElement instanceof \DOMElement) {
            return null;
        }

        foreach ($rootElement->childNodes as $child) {
            if (! $child instanceof \DOMElement) {
                continue;
            }
            if (($child->localName ?? '') !== 'file') {
                continue;
            }
            if ($child->getAttribute('source-language') === '') {
                $child->setAttribute('source-language', self::DEFAULT_SOURCE_LANGUAGE);
                $changed = true;
            }
        }

        return $changed ? $xliffDocument : null;
    }

    private function ensureV2SourceLanguage(XliffDocument $xliffDocument): ?XliffDocument
    {
        $rootElement = $xliffDocument->document->documentElement;
        if (! $rootElement instanceof \DOMElement) {
            return null;
        }

        if ($rootElement->getAttribute('srcLang') !== '') {
            return null;
        }

        $rootElement->setAttribute('srcLang', self::DEFAULT_SOURCE_LANGUAGE);

        return $xliffDocument;
    }
}
