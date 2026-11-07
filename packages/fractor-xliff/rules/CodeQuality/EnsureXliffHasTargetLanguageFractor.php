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
 * @see \a9f\FractorXliff\Tests\CodeQuality\EnsureXliffHasTargetLanguageFractor\EnsureXliffHasTargetLanguageFractorTest
 */
final class EnsureXliffHasTargetLanguageFractor implements XliffFractorRule, NoChangelogRequired
{
    public function refactor(XliffDocument $xliffDocument): ?XliffDocument
    {
        $language = $this->extractLanguageFromFilename($xliffDocument->file->getFileName());
        if ($language === null) {
            return null;
        }

        if ($xliffDocument->version === XliffVersion::V2_0) {
            return $this->addV2TargetLanguage($xliffDocument, $language);
        }

        return $this->addV1TargetLanguage($xliffDocument, $language);
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Add target-language attribute to localized XLIFF files where the filename starts with a 2-letter ISO language code',
            [new CodeSample(
                <<<'CODE_SAMPLE'
<!-- de.locallang.xlf -->
<?xml version="1.0" encoding="UTF-8"?>
<xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2">
    <file source-language="en" datatype="plaintext" original="messages">
        <body>
            <trans-unit id="label.hello">
                <source>Hello</source>
                <target>Hallo</target>
            </trans-unit>
        </body>
    </file>
</xliff>
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
<!-- de.locallang.xlf -->
<?xml version="1.0" encoding="UTF-8"?>
<xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2">
    <file source-language="en" target-language="de" datatype="plaintext" original="messages">
        <body>
            <trans-unit id="label.hello">
                <source>Hello</source>
                <target>Hallo</target>
            </trans-unit>
        </body>
    </file>
</xliff>
CODE_SAMPLE
            )]
        );
    }

    private function addV1TargetLanguage(XliffDocument $xliffDocument, string $language): ?XliffDocument
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
            if ($child->getAttribute('target-language') !== '') {
                continue;
            }

            $child->setAttribute('target-language', $language);
            $changed = true;
        }

        return $changed ? $xliffDocument : null;
    }

    private function addV2TargetLanguage(XliffDocument $xliffDocument, string $language): ?XliffDocument
    {
        $rootElement = $xliffDocument->document->documentElement;
        if (! $rootElement instanceof \DOMElement) {
            return null;
        }

        if ($rootElement->getAttribute('trgLang') !== '') {
            return null;
        }

        $rootElement->setAttribute('trgLang', $language);

        return $xliffDocument;
    }

    private function extractLanguageFromFilename(string $filename): ?string
    {
        if (\preg_match('/^([a-z]{2})\./i', $filename, $matches) !== 1) {
            return null;
        }

        return \strtolower($matches[1]);
    }
}
