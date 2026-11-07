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
 * @changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/12.0/Deprecation-97035-RequiredOptionInEvalKeyword.html
 * @changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/12.0/Feature-97035-UtilizeRequiredDirectlyInTCAFieldConfiguration.html
 * @see \a9f\Typo3Fractor\Tests\TYPO3v12\FlexForm\MigrateRequiredFlagFlexFormFractor\MigrateRequiredFlagFlexFormFractorTest
 */
final class MigrateRequiredFlagFlexFormFractor extends AbstractFlexformFractor
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

        if (! StringUtility::inList($evalListValue, 'required')) {
            return null;
        }

        $evalList = ArrayUtility::trimExplode(',', $evalListValue, true);

        // Remove "required" from $evalList
        $evalList = array_filter($evalList, static fn (string $eval): bool => $eval !== 'required');

        if ($evalList !== []) {
            // Write back filtered 'eval'
            $evalDomElement->nodeValue = '';
            $evalDomElement->appendChild($this->domDocument->createTextNode(implode(',', $evalList)));
        } elseif ($evalDomElement->parentNode instanceof \DOMElement) {
            // 'eval' is empty, remove whole configuration
            $evalDomElement->parentNode->removeChild($evalDomElement);
        }

        $requiredDomElement = $this->extractDomElementByKey($node, 'required');
        if (! $requiredDomElement instanceof \DOMElement) {
            $node->appendChild($this->domDocument->createElement('required', '1'));
        }

        return $node;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Migrate required flag', [new CodeSample(
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
                            <eval>trim,required</eval>
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
                            <eval>trim</eval>
                            <required>1</required>
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
