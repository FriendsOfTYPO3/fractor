<?php

declare(strict_types=1);

namespace a9f\Typo3Fractor\TYPO3v12\FlexForm;

use a9f\Fractor\Application\ValueObject\File;
use a9f\Fractor\RuleDoc\CodeSample\CodeSample;
use a9f\Fractor\RuleDoc\RuleDefinition;
use a9f\Typo3Fractor\AbstractFlexformFractor;
use a9f\Typo3Fractor\Helper\FlexFormHelperTrait;

/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/12.0/Deprecation-97109-TCATypeNoneColsOption.html
 * @see \a9f\Typo3Fractor\Tests\TYPO3v12\FlexForm\MigrateTypeNoneColsToSizeFlexFormFractor\MigrateTypeNoneColsToSizeFlexFormFractorTest
 */
final class MigrateTypeNoneColsToSizeFlexFormFractor extends AbstractFlexformFractor
{
    use FlexFormHelperTrait;

    private \DOMDocument $domDocument;

    public function canHandle(\DOMNode $node): bool
    {
        return parent::canHandle($node) && $node->nodeName === 'config';
    }

    public function beforeTraversal(File $file, \DOMDocument $rootNode): void
    {
        $this->file = $file;
        $this->domDocument = $rootNode;
    }

    public function refactor(\DOMNode $node): \DOMNode|null
    {
        if (! $node instanceof \DOMElement) {
            return null;
        }

        if (! $this->isConfigType($node, 'none')) {
            return null;
        }

        $colsElement = $this->extractDomElementByKey($node, 'cols');
        if (! $colsElement instanceof \DOMElement) {
            return null;
        }

        $testElement = $this->extractDomElementByKey($node, 'test');
        if ($testElement instanceof \DOMElement) {
            $this->changeTagName($this->domDocument, $testElement, 'aaa');
        }

        $this->removeChildElementFromDomElementByKey($node, 'size');
        $this->changeTagName($this->domDocument, $colsElement, 'size');

        return $node;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Migrates option cols to size for TCA type none', [new CodeSample(
            <<<'CODE_SAMPLE'
<T3DataStructure>
    <sheets>
        <sDEF>
            <ROOT>
                <sheetTitle>Sheet Title</sheetTitle>
                <type>array</type>
                <el>
                    <aColumn>
                        <config>
                            <type>none</type>
                            <cols>20</cols>
                        </config>
                    </aColumn>
                </el>
            </ROOT>
        </sDEF>
    </sheets>
</T3DataStructure>
CODE_SAMPLE
            ,
            <<<'CODE_SAMPLE'
<T3DataStructure>
    <sheets>
        <sDEF>
            <ROOT>
                <sheetTitle>Sheet Title</sheetTitle>
                <type>array</type>
                <el>
                    <aColumn>
                        <config>
                            <type>none</type>
                            <size>20</size>
                        </config>
                    </aColumn>
                </el>
            </ROOT>
        </sDEF>
    </sheets>
</T3DataStructure>
CODE_SAMPLE
        )]);
    }
}
