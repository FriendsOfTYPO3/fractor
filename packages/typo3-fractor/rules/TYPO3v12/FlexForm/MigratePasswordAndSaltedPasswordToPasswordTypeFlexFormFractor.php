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
 * @changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/12.0/Feature-97104-NewTCATypePassword.html
 * @see \a9f\Typo3Fractor\Tests\TYPO3v12\FlexForm\MigratePasswordAndSaltedPasswordToPasswordTypeFlexFormFractor\MigratePasswordAndSaltedPasswordToPasswordTypeFlexFormFractorTest
 */
final class MigratePasswordAndSaltedPasswordToPasswordTypeFlexFormFractor extends AbstractFlexformFractor
{
    use FlexFormHelperTrait;

    /**
     * @var string
     */
    private const PASSWORD = 'password';

    /**
     * @var string
     */
    private const SALTED_PASSWORD = 'saltedPassword';

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

        if (! StringUtility::inList($evalListValue, self::PASSWORD)
            && ! StringUtility::inList($evalListValue, self::SALTED_PASSWORD)
        ) {
            return null;
        }

        // Set the TCA type to "password"
        $this->changeTcaType($this->domDocument, $node, self::PASSWORD);

        // Remove 'max' and 'search' config
        $this->removeChildElementFromDomElementByKey($node, 'max');
        $this->removeChildElementFromDomElementByKey($node, 'search');

        $evalList = ArrayUtility::trimExplode(',', $evalListValue, true);

        // Disable password hashing, if eval=password is used standalone
        if (in_array('password', $evalList, true) && ! in_array('saltedPassword', $evalList, true)) {
            $node->appendChild($this->domDocument->createElement('hashed', '0'));
        }

        if (in_array('null', $evalList, true)) {
            // Set "eval" to "null", since it's currently defined and the only allowed "eval" for type=password
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
        return new RuleDefinition('Migrate password and salted password to password type', [new CodeSample(
            <<<'CODE_SAMPLE'
<T3DataStructure>
    <sheets>
        <sDEF>
            <ROOT>
                <sheetTitle>Sheet Title</sheetTitle>
                <type>array</type>
                <el>
                    <password_field>
                        <config>
                            <type>input</type>
                            <eval>trim,password,saltedPassword</eval>
                        </config>
                    </password_field>
                    <another_password_field>
                        <config>
                            <type>input</type>
                            <eval>trim,password</eval>
                        </config>
                    </another_password_field>
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
                    <password_field>
                        <config>
                            <type>password</type>
                        </config>
                    </password_field>
                    <another_password_field>
                        <config>
                            <type>password</type>
                            <hashed>false</hashed>
                        </config>
                    </another_password_field>
                </el>
            </ROOT>
        </sDEF>
    </sheets>
</T3DataStructure>
CODE_SAMPLE
        )]);
    }
}
