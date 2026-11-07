<?php

declare(strict_types=1);

namespace a9f\Typo3Fractor\TYPO3v12\FlexForm;

use a9f\Fractor\Application\ValueObject\File;
use a9f\Fractor\Helper\ArrayUtility;
use a9f\Fractor\RuleDoc\CodeSample\CodeSample;
use a9f\Fractor\RuleDoc\RuleDefinition;
use a9f\Typo3Fractor\AbstractFlexformFractor;
use a9f\Typo3Fractor\Helper\FlexFormHelperTrait;

/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/12.0/Feature-97271-NewTCATypeColor.html
 * @see \a9f\Typo3Fractor\Tests\TYPO3v12\FlexForm\MigrateRenderTypeColorpickerToTypeColorFlexFormFractor\MigrateRenderTypeColorpickerToTypeColorFlexFormFractorTest
 */
final class MigrateRenderTypeColorpickerToTypeColorFlexFormFractor extends AbstractFlexformFractor
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

        if (! $this->isConfigType($node, 'input')) {
            return null;
        }

        if (! $this->configIsOfRenderType($node, 'colorpicker')) {
            return null;
        }

        // Set the TCA type to "color"
        $this->changeTcaType($this->domDocument, $node, 'color');

        // Remove 'max' and 'renderType' config
        $this->removeChildElementFromDomElementByKey($node, 'max');
        $this->removeChildElementFromDomElementByKey($node, 'renderType');

        $evalDomElement = $this->extractDomElementByKey($node, 'eval');
        if (! $evalDomElement instanceof \DOMElement) {
            return null;
        }

        $evalListValue = $evalDomElement->nodeValue;
        if (! is_string($evalListValue)) {
            return null;
        }

        $evalList = ArrayUtility::trimExplode(',', $evalListValue, true);

        if (in_array('null', $evalList, true)) {
            // Set "eval" to "null", since it's currently defined and the only allowed "eval" for type=color
            $evalDomElement->nodeValue = '';
            $evalDomElement->appendChild($this->domDocument->createTextNode('null'));
        } elseif ($evalDomElement->parentNode instanceof \DOMElement) {
            // 'eval' is empty, remove whole configuration
            $evalDomElement->parentNode->removeChild($evalDomElement);
        }

        return $node;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Migrate renderType colorpicker to type color', [new CodeSample(
            <<<'CODE_SAMPLE'
<T3DataStructure>
    <sheets>
        <sDEF>
            <ROOT>
                <sheetTitle>Sheet Title</sheetTitle>
                <type>array</type>
                <el>
                    <a_color_field>
                        <config>
                            <type>input</type>
                            <renderType>colorpicker</renderType>
                            <required>1</required>
                            <size>20</size>
                            <max>1234</max>
                            <eval>trim,null</eval>
                            <valuePicker>
                                <items type="array">
                                    <numIndex index="0" type="array">
                                        <numIndex index="0">typo3 orange</numIndex>
                                        <numIndex index="1">#FF8700</numIndex>
                                    </numIndex>
                                </items>
                            </valuePicker>
                        </config>
                    </a_color_field>
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
                    <a_color_field>
                        <config>
                            <type>color</type>
                            <required>1</required>
                            <size>20</size>
                            <valuePicker>
                                <items type="array">
                                    <numIndex index="0" type="array">
                                        <numIndex index="0">typo3 orange</numIndex>
                                        <numIndex index="1">#FF8700</numIndex>
                                    </numIndex>
                                </items>
                            </valuePicker>
                        </config>
                    </a_color_field>
                </el>
            </ROOT>
        </sDEF>
    </sheets>
</T3DataStructure>
CODE_SAMPLE
        )]);
    }
}
