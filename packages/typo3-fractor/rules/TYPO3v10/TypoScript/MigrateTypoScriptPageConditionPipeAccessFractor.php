<?php

declare(strict_types=1);

namespace a9f\Typo3Fractor\TYPO3v10\TypoScript;

use a9f\Fractor\RuleDoc\CodeSample\CodeSample;
use a9f\Fractor\RuleDoc\RuleDefinition;
use a9f\FractorTypoScript\AbstractTypoScriptFractor;
use Helmich\TypoScriptParser\Parser\AST\ConditionalStatement;
use Helmich\TypoScriptParser\Parser\AST\Statement;

/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/9.4/Feature-85829-ImplementSymfonyExpressionLanguageForTypoScriptConditions.html
 * @see \a9f\Typo3Fractor\Tests\TYPO3v10\TypoScript\MigrateTypoScriptPageConditionPipeAccessFractor\MigrateTypoScriptPageConditionPipeAccessFractorTest
 */
final class MigrateTypoScriptPageConditionPipeAccessFractor extends AbstractTypoScriptFractor
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Migrate page pipe access in TypoScript conditions to bracket array access syntax',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
[page|uid = 2]
    page = PAGE
    page.10 = TEXT
    page.10.value = Hello
[end]
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
[page["uid"] == 2]
    page = PAGE
    page.10 = TEXT
    page.10.value = Hello
[end]
CODE_SAMPLE
                ),
                new CodeSample(
                    <<<'CODE_SAMPLE'
[page|layout == 1]
    page = PAGE
    page.10 = TEXT
    page.10.value = Layout 1
[end]
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
[page["layout"] == 1]
    page = PAGE
    page.10 = TEXT
    page.10.value = Layout 1
[end]
CODE_SAMPLE
                ),
            ]
        );
    }

    public function refactor(Statement $statement): ?Statement
    {
        if (! $statement instanceof ConditionalStatement) {
            return null;
        }

        $condition = $statement->condition;
        $originalCondition = $condition;

        $condition = $this->migratePagePipeAccess($condition);

        if ($condition === $originalCondition) {
            return null;
        }

        $statement->condition = $condition;

        return $statement;
    }

    private function migratePagePipeAccess(string $condition): string
    {
        return (string) preg_replace_callback(
            '/(?<![\w:])page\|(\w+)\s*([><=!]{1,2})\s*(\S+)/',
            static function (array $matches): string {
                $field = $matches[1];
                $operator = trim($matches[2]);
                $value = trim($matches[3]);

                if ($operator === '=') {
                    $operator = '==';
                }

                return sprintf('page["%s"] %s %s', $field, $operator, $value);
            },
            $condition
        );
    }
}
