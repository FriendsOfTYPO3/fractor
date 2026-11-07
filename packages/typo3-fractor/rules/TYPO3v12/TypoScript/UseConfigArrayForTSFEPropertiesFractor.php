<?php

declare(strict_types=1);

namespace a9f\Typo3Fractor\TYPO3v12\TypoScript;

use a9f\Fractor\RuleDoc\CodeSample\CodeSample;
use a9f\Fractor\RuleDoc\RuleDefinition;
use a9f\FractorTypoScript\AbstractTypoScriptFractor;
use Helmich\TypoScriptParser\Parser\AST\Operator\Assignment;
use Helmich\TypoScriptParser\Parser\AST\Statement;

/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/12.0/Deprecation-97866-VariousPublicTSFEProperties.html
 */
final class UseConfigArrayForTSFEPropertiesFractor extends AbstractTypoScriptFractor
{
    private const DEPRECATED_PUBLIC_PROPERTIES = [
        'intTarget',
        'extTarget',
        'fileTarget',
        'spamProtectEmailAddresses',
        'baseUrl',
    ];

    public function refactor(Statement $statement): null|Statement
    {
        if (! $statement instanceof Assignment) {
            return null;
        }

        if (! str_ends_with($statement->object->absoluteName, '.data')) {
            return null;
        }

        if (! str_starts_with($statement->value->value, 'TSFE:')) {
            return null;
        }

        $property = substr($statement->value->value, 5);

        if (! in_array($property, self::DEPRECATED_PUBLIC_PROPERTIES, true)) {
            return null;
        }

        $statement->value->value = sprintf('TSFE:config|config|%s', $property);

        return $statement;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Use config array in TSFE instead of deprecated class properties', [new CodeSample(
            <<<'CODE_SAMPLE'
page.10.data = TSFE:fileTarget
CODE_SAMPLE
            ,
            <<<'CODE_SAMPLE'
page.10.data = TSFE:config|config|fileTarget
CODE_SAMPLE
        )]);
    }
}
