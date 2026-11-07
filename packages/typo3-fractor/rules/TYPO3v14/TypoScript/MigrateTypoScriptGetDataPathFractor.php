<?php

declare(strict_types=1);

namespace a9f\Typo3Fractor\TYPO3v14\TypoScript;

use a9f\Fractor\RuleDoc\CodeSample\CodeSample;
use a9f\Fractor\RuleDoc\RuleDefinition;
use a9f\FractorTypoScript\AbstractTypoScriptFractor;
use Helmich\TypoScriptParser\Parser\AST\Operator\Assignment;
use Helmich\TypoScriptParser\Parser\AST\Statement;

/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/14.0/Deprecation-107537-GetDataPath.html
 * @see \a9f\Typo3Fractor\Tests\TYPO3v14\TypoScript\MigrateTypoScriptGetDataPathFractor\MigrateTypoScriptGetDataPathFractorTest
 */
final class MigrateTypoScriptGetDataPathFractor extends AbstractTypoScriptFractor
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Migrate TypoScript getData \"path\"', [new CodeSample(
            <<<'CODE_SAMPLE'
page.20 = TEXT
page.20 {
    data = path : EXT:core/Resources/Public/Icons/Extension.svg
}
CODE_SAMPLE
            ,
            <<<'CODE_SAMPLE'
page.20 = TEXT
page.20 {
    data = asset : EXT:core/Resources/Public/Icons/Extension.svg
}
CODE_SAMPLE
        )]);
    }

    public function refactor(Statement $statement): ?Statement
    {
        if (! $statement instanceof Assignment) {
            return null;
        }

        if (! str_ends_with($statement->object->relativeName, 'data')) {
            return null;
        }

        $scalar = $statement->value;

        if (preg_match('/^\s*path\s*:/i', $scalar->value)) {
            $scalar->value = (string) preg_replace('/^(\s*)path(\s*:)/i', '$1asset$2', $scalar->value);
            return $statement;
        }

        return null;
    }
}
