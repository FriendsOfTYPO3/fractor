<?php

declare(strict_types=1);

namespace a9f\Typo3Fractor\TYPO3v10\FlexForm;

use a9f\Typo3Fractor\AbstractFlexformFractor;
use a9f\Typo3Fractor\Helper\FlexFormHelperTrait;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/10.1/Feature-88907-AlwaysEnableFilterInSelectMultipleSideBySideFields.html
 * @see \a9f\Typo3Fractor\Tests\TYPO3v10\FlexForm\RemoveEnableMultiSelectFilterTextfieldFractor\RemoveEnableMultiSelectFilterTextfieldFractorTest
 */
final class RemoveEnableMultiSelectFilterTextfieldFractor extends AbstractFlexformFractor
{
    use FlexFormHelperTrait;

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Remove `\"enableMultiSelectFilterTextfield\" => true` as its default from render type \"selectMultipleSideBySide\"',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
<T3DataStructure>
    <sheets>
        <sDEF>
            <ROOT>
                <sheetTitle>Sheet Title</sheetTitle>
                <type>array</type>
                <el>
                    <tsconfig_includes>
                        <config>
                            <type>select</type>
                            <renderType>selectMultipleSideBySide</renderType>
                            <size>10</size>
                            <enableMultiSelectFilterTextfield>1</enableMultiSelectFilterTextfield>
                        </config>
                    </tsconfig_includes>
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
                    <tsconfig_includes>
                        <config>
                            <type>select</type>
                            <renderType>selectMultipleSideBySide</renderType>
                            <size>10</size>
                        </config>
                    </tsconfig_includes>
                </el>
            </ROOT>
        </sDEF>
    </sheets>
</T3DataStructure>
CODE_SAMPLE
                ),
            ]
        );
    }

    public function canHandle(\DOMNode $node): bool
    {
        return parent::canHandle($node) && $node->nodeName === 'config';
    }

    public function refactor(\DOMNode $node): ?\DOMNode
    {
        if (! $node instanceof \DOMElement) {
            return null;
        }

        if (! $this->isConfigType($node, 'select')) {
            return null;
        }

        if (! $this->configIsOfRenderType($node, 'selectMultipleSideBySide')) {
            return null;
        }

        if (! $this->hasKey($node, 'enableMultiSelectFilterTextfield')) {
            return null;
        }

        $this->removeChildElementFromDomElementByKey($node, 'enableMultiSelectFilterTextfield');

        return $node;
    }
}
