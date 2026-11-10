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
 * @changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/12.0/Deprecation-97384-TCAOptionNullable.html
 * @changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/12.0/Feature-97384-TCAOptionNullable.html
 * @see \a9f\Typo3Fractor\Tests\TYPO3v12\FlexForm\MigrateNullFlagFlexFormFractor\MigrateNullFlagFlexFormFractorTest
 */
final class MigrateNullFlagFlexFormFractor extends AbstractFlexformFractor
{
    use FlexFormHelperTrait;

    /**
     * @var string
     */
    private const NULL = 'null';

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

        if (! StringUtility::inList($evalListValue, self::NULL)) {
            return null;
        }

        $evalList = ArrayUtility::trimExplode(',', $evalListValue, true);

        // Remove "null" from $evalList
        $evalList = array_filter($evalList, static fn (string $eval): bool => $eval !== self::NULL);

        if ($evalList !== []) {
            // Write back filtered 'eval'
            $evalDomElement->nodeValue = '';
            $evalDomElement->appendChild($this->domDocument->createTextNode(implode(',', $evalList)));
        } elseif ($evalDomElement->parentNode instanceof \DOMElement) {
            // 'eval' is empty, remove whole configuration
            $evalDomElement->parentNode->removeChild($evalDomElement);
        }

        $nullableDomElement = $this->extractDomElementByKey($node, 'nullable');
        if ($nullableDomElement instanceof \DOMElement && $nullableDomElement->parentNode instanceof \DOMElement) {
            $nullableDomElement->parentNode->removeChild($nullableDomElement);
        }

        $node->appendChild($this->domDocument->createElement('nullable', 'true'));

        return $node;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Migrate null flag', [new CodeSample(
            <<<'CODE_SAMPLE'
<T3DataStructure>
    <sheets>
        <sDEF>
            <ROOT>
                <sheetTitle>Sheet Title</sheetTitle>
                <type>array</type>
                <el>
                    <aFlexField>
                        <config>
                            <eval>null</eval>
                        </config>
                    </aFlexField>
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
                    <aFlexField>
                        <config>
                            <nullable>true</nullable>
                        </config>
                    </aFlexField>
                </el>
            </ROOT>
        </sDEF>
    </sheets>
</T3DataStructure>
CODE_SAMPLE
        )]);
    }
}
