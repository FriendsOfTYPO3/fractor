<?php

declare(strict_types=1);

namespace a9f\Typo3Fractor\TYPO3v12\FlexForm;

use a9f\Fractor\Application\ValueObject\File;
use a9f\Fractor\RuleDoc\CodeSample\CodeSample;
use a9f\Fractor\RuleDoc\RuleDefinition;
use a9f\Typo3Fractor\AbstractFlexformFractor;
use a9f\Typo3Fractor\Helper\FlexFormHelperTrait;

/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/12.3/Deprecation-99739-IndexedArrayKeysForTCAItems.html
 * @changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/12.3/Feature-99739-AssociativeArrayKeysForTCAItems.html
 * @see \a9f\Typo3Fractor\Tests\TYPO3v12\FlexForm\MigrateItemsIndexedKeysToAssociativeFractor\MigrateItemsIndexedKeysToAssociativeFractorTest
 */
final class MigrateItemsIndexedKeysToAssociativeFractor extends AbstractFlexformFractor
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

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Migrates indexed item array keys to associative for type select, radio and check', [
            new CodeSample(
                <<<'CODE_SAMPLE'
<T3DataStructure>
    <sheets>
        <sDEF>
            <ROOT>
                <sheetTitle>Sheet Title</sheetTitle>
                <type>array</type>
                <el>
                    <selectSingleColumn>
                        <config>
                            <type>select</type>
                            <renderType>selectSingle</renderType>
                            <items type="array">
                                <numIndex index="0" type="array">
                                    <numIndex index="0"/>
                                    <numIndex index="1"/>
                                </numIndex>
                                <numIndex index="1" type="array">
                                    <numIndex index="0">Label 1</numIndex>
                                    <numIndex index="1">1</numIndex>
                                </numIndex>
                                <numIndex index="2" type="array">
                                    <numIndex index="0">Label 2</numIndex>
                                    <numIndex index="1">2</numIndex>
                                </numIndex>
                                <numIndex index="3" type="array">
                                    <numIndex index="0">Label 3</numIndex>
                                    <numIndex index="1">3</numIndex>
                                </numIndex>
                            </items>
                        </config>
                    </selectSingleColumn>
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
                    <selectSingleColumn>
                        <config>
                            <type>select</type>
                            <renderType>selectSingle</renderType>
                            <items type="array">
                                <numIndex index="0" type="array">
                                    <label/>
                                    <value/>
                                </numIndex>
                                <numIndex index="1" type="array">
                                    <label>Label 1</label>
                                    <value>1</value>
                                </numIndex>
                                <numIndex index="2" type="array">
                                    <label>Label 2</label>
                                    <value>2</value>
                                </numIndex>
                                <numIndex index="3" type="array">
                                    <label>Label 3</label>
                                    <value>3</value>
                                </numIndex>
                            </items>
                        </config>
                    </selectSingleColumn>
                </el>
            </ROOT>
        </sDEF>
    </sheets>
</T3DataStructure>
CODE_SAMPLE
            ),
        ]);
    }

    public function refactor(\DOMNode $node): \DOMNode|null
    {
        if (! $node instanceof \DOMElement) {
            return null;
        }

        if (
            ! $this->isConfigType($node, 'select')
            && ! $this->isConfigType($node, 'radio')
            && ! $this->isConfigType($node, 'check')
        ) {
            return null;
        }

        $hasAstBeenChanged = false;
        $exprArrayItemToChange = $this->extractDomElementByKey($node, 'items');
        if (! $exprArrayItemToChange instanceof \DOMElement) {
            return null;
        }

        foreach ($exprArrayItemToChange->childNodes as $itemNode) {
            if (! $itemNode instanceof \DOMElement) {
                continue;
            }

            $numIndexZero = $this->extractDomElementFromArrayByIndex($itemNode, '0');
            if ($numIndexZero instanceof \DOMElement) {
                $this->changeTagName($this->domDocument, $numIndexZero, 'label');
                $hasAstBeenChanged = true;
            }

            if (! $this->isConfigType($node, 'check')) {
                $numIndexOne = $this->extractDomElementFromArrayByIndex($itemNode, '1');
                if ($numIndexOne instanceof \DOMElement) {
                    $this->changeTagName($this->domDocument, $numIndexOne, 'value');
                    $hasAstBeenChanged = true;
                }
            }

            if ($this->isConfigType($node, 'select')) {
                $numIndexTwo = $this->extractDomElementFromArrayByIndex($itemNode, '2');
                if ($numIndexTwo instanceof \DOMElement) {
                    $this->changeTagName($this->domDocument, $numIndexTwo, 'icon');
                    $hasAstBeenChanged = true;
                }

                $numIndexThree = $this->extractDomElementFromArrayByIndex($itemNode, '3');
                if ($numIndexThree instanceof \DOMElement) {
                    $this->changeTagName($this->domDocument, $numIndexThree, 'group');
                    $hasAstBeenChanged = true;
                }

                $numIndexFour = $this->extractDomElementFromArrayByIndex($itemNode, '4');
                if ($numIndexFour instanceof \DOMElement) {
                    $this->changeTagName($this->domDocument, $numIndexFour, 'description');
                    $hasAstBeenChanged = true;
                }
            }
        }

        return $hasAstBeenChanged ? $node : null;
    }
}
