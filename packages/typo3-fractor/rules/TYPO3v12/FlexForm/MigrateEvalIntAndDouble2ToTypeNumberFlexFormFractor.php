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
 * @changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/12.0/Feature-97193-NewTCATypeNumber.html
 * @see \a9f\Typo3Fractor\Tests\TYPO3v12\FlexForm\MigrateEvalIntAndDouble2ToTypeNumberFlexFormFractor\MigrateEvalIntAndDouble2ToTypeNumberFlexFormFractorTest
 */
final class MigrateEvalIntAndDouble2ToTypeNumberFlexFormFractor extends AbstractFlexformFractor
{
    use FlexFormHelperTrait;

    /**
     * @var string
     */
    private const INT = 'int';

    /**
     * @var string
     */
    private const DOUBLE2 = 'double2';

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

        if (! $this->isConfigType($node, 'input') || $this->hasRenderType($node)) {
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

        if (! StringUtility::inList($evalListValue, self::INT)
            && ! StringUtility::inList($evalListValue, self::DOUBLE2)
        ) {
            return null;
        }

        $evalList = ArrayUtility::trimExplode(',', $evalListValue, true);

        // Remove "int" from $evalList
        $evalList = array_filter(
            $evalList,
            static fn (string $eval): bool => $eval !== self::INT && $eval !== self::DOUBLE2
        );

        if ($evalList !== []) {
            // Write back filtered 'eval'
            $evalDomElement->nodeValue = '';
            $evalDomElement->appendChild($this->domDocument->createTextNode(implode(',', $evalList)));
        } elseif ($evalDomElement->parentNode instanceof \DOMElement) {
            // 'eval' is empty, remove whole configuration
            $evalDomElement->parentNode->removeChild($evalDomElement);
        }

        $this->changeTcaType($this->domDocument, $node, 'number');

        if (StringUtility::inList($evalListValue, self::DOUBLE2)) {
            $node->appendChild($this->domDocument->createElement('format', 'decimal'));
        }

        return $node;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Migrate eval int and double2 to type number', [new CodeSample(
            <<<'CODE_SAMPLE'
<T3DataStructure>
    <sheets>
        <sDEF>
            <ROOT>
                <sheetTitle>Sheet Title</sheetTitle>
                <type>array</type>
                <el>
                    <int_field>
                        <label>int field</label>
                        <config>
                            <type>input</type>
                            <eval>int</eval>
                        </config>
                    </int_field>
                    <double2_field>
                        <label>double2 field</label>
                        <config>
                            <type>input</type>
                            <eval>double2</eval>
                        </config>
                    </double2_field>
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
                    <int_field>
                        <label>int field</label>
                        <config>
                            <type>number</type>
                        </config>
                    </int_field>
                    <double2_field>
                        <label>double2 field</label>
                        <config>
                            <type>number</type>
                            <format>decimal</format>
                        </config>
                    </double2_field>
                </el>
            </ROOT>
        </sDEF>
    </sheets>
</T3DataStructure>
CODE_SAMPLE
        )]);
    }
}
