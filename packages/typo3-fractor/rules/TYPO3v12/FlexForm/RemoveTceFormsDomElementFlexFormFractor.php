<?php

declare(strict_types=1);

namespace a9f\Typo3Fractor\TYPO3v12\FlexForm;

use a9f\Fractor\RuleDoc\CodeSample\CodeSample;
use a9f\Fractor\RuleDoc\RuleDefinition;
use a9f\FractorXml\DomDocumentIterator;
use a9f\FractorXml\Exception\ShouldNotHappenException;
use a9f\Typo3Fractor\AbstractFlexformFractor;
use a9f\Typo3Fractor\Helper\FlexFormHelperTrait;

/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/12.0/Deprecation-97126-TCEformsRemovedInFlexForm.html
 * @see \a9f\Typo3Fractor\Tests\TYPO3v12\FlexForm\RemoveTceFormsDomElementFlexFormFractor\RemoveTceFormsDomElementFlexFormFractorTest
 */
final class RemoveTceFormsDomElementFlexFormFractor extends AbstractFlexformFractor
{
    use FlexFormHelperTrait;

    public function canHandle(\DOMNode $node): bool
    {
        return parent::canHandle($node) && $node->nodeName === 'TCEforms';
    }

    public function refactor(\DOMNode $node): int|null
    {
        $parent = $node->parentNode;
        if (! $parent instanceof \DOMNode) {
            return null;
        }

        // the iterator is invalidated/modified if the nodes change during traversal => create a copy here to prevent this
        $childNodes = iterator_to_array($node->childNodes->getIterator());
        foreach ($childNodes as $child) {
            if (! $child instanceof \DOMNode) {
                throw new ShouldNotHappenException(sprintf('Expected DOMNode, got %s', $child::class), 1718997872);
            }
            $parent->insertBefore($child->cloneNode(true), $node);

            $node->removeChild($child);
        }

        return DomDocumentIterator::REMOVE_NODE;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove TCEForms key from all elements in data structure', [new CodeSample(
            <<<'CODE_SAMPLE'
<T3DataStructure>
    <ROOT>
        <TCEforms>
            <sheetTitle>aTitle</sheetTitle>
        </TCEforms>
        <type>array</type>
        <el>
            <aFlexField>
                <TCEforms>
                    <label>aFlexFieldLabel</label>
                    <config>
                        <type>input</type>
                    </config>
                </TCEforms>
            </aFlexField>
        </el>
    </ROOT>
</T3DataStructure>
CODE_SAMPLE
            ,
            <<<'CODE_SAMPLE'
<T3DataStructure>
    <ROOT>
        <sheetTitle>aTitle</sheetTitle>
        <type>array</type>
        <el>
            <aFlexField>
                <label>aFlexFieldLabel</label>
                <config>
                    <type>input</type>
                </config>
            </aFlexField>
        </el>
    </ROOT>
</T3DataStructure>
CODE_SAMPLE
        )]);
    }
}
