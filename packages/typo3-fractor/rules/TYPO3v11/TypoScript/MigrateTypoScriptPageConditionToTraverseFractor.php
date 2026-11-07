<?php

declare(strict_types=1);

namespace a9f\Typo3Fractor\TYPO3v11\TypoScript;

use a9f\Fractor\RuleDoc\CodeSample\CodeSample;
use a9f\Fractor\RuleDoc\RuleDefinition;
use a9f\FractorTypoScript\AbstractTypoScriptFractor;
use Helmich\TypoScriptParser\Parser\AST\ConditionalStatement;
use Helmich\TypoScriptParser\Parser\AST\Statement;

/**
 * @changelog https://docs.typo3.org/m/typo3/reference-typoscript/11.5/en-us/Conditions/Index.html
 * @see \a9f\Typo3Fractor\Tests\TYPO3v11\TypoScript\MigrateTypoScriptPageConditionToTraverseFractor\MigrateTypoScriptPageConditionToTraverseFractorTest
 */
final class MigrateTypoScriptPageConditionToTraverseFractor extends AbstractTypoScriptFractor
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Migrate page["field"] to traverse(page, "field") in TypoScript conditions for safe access',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
[page["uid"] == 1]
    page = PAGE
    page.10 = TEXT
    page.10.value = Hello
[end]
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
[traverse(page, "uid") == 1]
    page = PAGE
    page.10 = TEXT
    page.10.value = Hello
[end]
CODE_SAMPLE
                ),
                new CodeSample(
                    <<<'CODE_SAMPLE'
[page["backend_layout"] == "pagets__home"]
    page = PAGE
    page.10 = TEXT
    page.10.value = Home layout
[end]
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
[traverse(page, "backend_layout") == "pagets__home"]
    page = PAGE
    page.10 = TEXT
    page.10.value = Home layout
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

        $condition = $this->migratePageBracketAccess($condition);

        if ($condition === $originalCondition) {
            return null;
        }

        $statement->condition = $condition;

        return $statement;
    }

    private function migratePageBracketAccess(string $condition): string
    {
        return (string) preg_replace('/page\["(\w+)"\]/', 'traverse(page, "$1")', $condition);
    }
}
