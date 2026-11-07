<?php

declare(strict_types=1);

namespace a9f\Typo3Fractor\TYPO3v12\FlexForm;

use a9f\Fractor\Application\ValueObject\File;
use a9f\Fractor\RuleDoc\CodeSample\CodeSample;
use a9f\Fractor\RuleDoc\RuleDefinition;
use a9f\Typo3Fractor\AbstractFlexformFractor;
use a9f\Typo3Fractor\Helper\FlexFormHelperTrait;

/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/12.0/Feature-96983-TCATypeFolder.html
 * @see \a9f\Typo3Fractor\Tests\TYPO3v12\FlexForm\MigrateInternalTypeFolderToTypeFolderFlexFormFractor\MigrateInternalTypeFolderToTypeFolderFlexFormFractorTest
 */
final class MigrateInternalTypeFolderToTypeFolderFlexFormFractor extends AbstractFlexformFractor
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

        if (! $this->isConfigType($node, 'group') || ! $this->hasKey($node, 'internal_type')) {
            return null;
        }

        $internalTypeDomElement = $this->extractDomElementByKey($node, 'internal_type');
        if (! $internalTypeDomElement instanceof \DOMElement) {
            return null;
        }

        // Unset
        if ($internalTypeDomElement->parentNode instanceof \DOMElement) {
            $internalTypeDomElement->parentNode->removeChild($internalTypeDomElement);
        }

        if ($internalTypeDomElement->nodeValue === 'folder') {
            $this->changeTcaType($this->domDocument, $node, 'folder');
        }

        return $node;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Migrates TCA internal_type into new new TCA type folder', [new CodeSample(
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
                            <type>group</type>
                            <internal_type>folder</internal_type>
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
                            <type>folder</type>
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
