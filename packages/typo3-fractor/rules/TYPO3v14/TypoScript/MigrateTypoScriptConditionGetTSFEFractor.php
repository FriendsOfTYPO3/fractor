<?php

declare(strict_types=1);

namespace a9f\Typo3Fractor\TYPO3v14\TypoScript;

use a9f\Fractor\RuleDoc\CodeSample\CodeSample;
use a9f\Fractor\RuleDoc\RuleDefinition;
use a9f\FractorTypoScript\AbstractTypoScriptFractor;
use Helmich\TypoScriptParser\Parser\AST\ConditionalStatement;
use Helmich\TypoScriptParser\Parser\AST\Statement;

/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/14.0/Breaking-107473-TypoScriptConditionFunctionGetTSFERemoved.html
 * @see \a9f\Typo3Fractor\Tests\TYPO3v14\TypoScript\MigrateTypoScriptConditionGetTSFEFractor\MigrateTypoScriptConditionGetTSFEFractorTest
 */
final class MigrateTypoScriptConditionGetTSFEFractor extends AbstractTypoScriptFractor
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Migrate TypoScript condition function getTSFE()', [new CodeSample(
            <<<'CODE_SAMPLE'
[getTSFE() && getTSFE().id == 42]
    temp.foo = 42
[end]
CODE_SAMPLE
            ,
            <<<'CODE_SAMPLE'
[request?.getPageArguments()?.getPageId() == 42]
    temp.foo = 42
[end]
CODE_SAMPLE
        )]);
    }

    public function refactor(Statement $statement): ?Statement
    {
        if ($this->shouldSkip($statement)) {
            return null;
        }

        /** @var ConditionalStatement $statement */
        $condition = str_replace('getTSFE() && ', '', $statement->condition);

        $statement->condition = str_replace(
            ['getTSFE().id', 'getTSFE()?.id'],
            'request?.getPageArguments()?.getPageId()',
            $condition
        );

        return $statement;
    }

    private function shouldSkip(Statement $statement): bool
    {
        if (! $statement instanceof ConditionalStatement) {
            return true;
        }

        if (! str_contains($statement->condition, 'getTSFE')) {
            return true;
        }

        return false;
    }
}
