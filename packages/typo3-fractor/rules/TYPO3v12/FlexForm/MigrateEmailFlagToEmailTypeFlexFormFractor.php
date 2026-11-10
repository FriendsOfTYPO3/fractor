<?php

declare(strict_types=1);

namespace a9f\Typo3Fractor\TYPO3v12\FlexForm;

use a9f\Fractor\Application\ValueObject\File;
use a9f\Fractor\Helper\ArrayUtility;
use a9f\Fractor\Helper\StringUtility;
use a9f\Fractor\RuleDoc\CodeSample\CodeSample;
use a9f\Fractor\RuleDoc\RuleDefinition;
use a9f\Typo3Fractor\AbstractFlexformFractor;
use a9f\Typo3Fractor\Helper\FlexFormHelperTrait;

/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/12.0/Feature-97013-NewTCATypeEmail.html
 * @see \a9f\Typo3Fractor\Tests\TYPO3v12\FlexForm\MigrateEmailFlagToEmailTypeFlexFormFractor\MigrateEmailFlagToEmailTypeFlexFormFractorTest
 */
final class MigrateEmailFlagToEmailTypeFlexFormFractor extends AbstractFlexformFractor
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

        if (! $this->hasKey($node, 'eval')) {
            return null;
        }

        $evalDomElement = $this->extractDomElementByKey($node, 'eval');
        if (! $evalDomElement instanceof \DOMElement) {
            return null;
        }

        $evalListValue = $evalDomElement->nodeValue;
        if (! is_string($evalListValue)) {
            return null;
        }

        if (! StringUtility::inList($evalListValue, 'email')) {
            return null;
        }

        // Set the TCA type to "email"
        $this->changeTcaType($this->domDocument, $node, 'email');

        $this->removeChildElementFromDomElementByKey($node, 'max');

        $evalList = ArrayUtility::trimExplode(',', $evalListValue, true);

        // Remove "null" and "trim" from $evalList
        $evalList = array_filter($evalList, static fn (string $eval): bool => $eval !== 'email' && $eval !== 'trim');

        if ($evalList !== []) {
            // Write back filtered 'eval'
            $evalDomElement->nodeValue = '';
            $evalDomElement->appendChild($this->domDocument->createTextNode(implode(',', $evalList)));
        } elseif ($evalDomElement->parentNode instanceof \DOMElement) {
            // 'eval' is empty, remove whole configuration
            $evalDomElement->parentNode->removeChild($evalDomElement);
        }

        return $node;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Migrate email flag to email type', [new CodeSample(
            <<<'CODE_SAMPLE'
<T3DataStructure>
    <sheets>
        <sDEF>
            <ROOT>
                <sheetTitle>Sheet Title</sheetTitle>
                <type>array</type>
                <el>
                    <email_field>
                        <config>
                            <type>input</type>
                            <eval>trim,email</eval>
                            <max>255</max>
                        </config>
                    </email_field>
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
                    <email_field>
                        <config>
                            <type>email</type>
                        </config>
                    </email_field>
                </el>
            </ROOT>
        </sDEF>
    </sheets>
</T3DataStructure>
CODE_SAMPLE
        )]);
    }
}
