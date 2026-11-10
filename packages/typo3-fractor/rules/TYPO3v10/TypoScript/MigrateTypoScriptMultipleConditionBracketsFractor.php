<?php

declare(strict_types=1);

namespace a9f\Typo3Fractor\TYPO3v10\TypoScript;

use a9f\Fractor\RuleDoc\CodeSample\CodeSample;
use a9f\Fractor\RuleDoc\RuleDefinition;
use a9f\FractorTypoScript\AbstractTypoScriptFractor;
use Helmich\TypoScriptParser\Parser\AST\ConditionalStatement;
use Helmich\TypoScriptParser\Parser\AST\Statement;

/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/9.5/Deprecation-86068-OldConditionSyntax.html
 * @see \a9f\Typo3Fractor\Tests\TYPO3v10\TypoScript\MigrateTypoScriptMultipleConditionBracketsFractor\MigrateTypoScriptMultipleConditionBracketsFractorTest
 */
final class MigrateTypoScriptMultipleConditionBracketsFractor extends AbstractTypoScriptFractor
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Merge multiple TypoScript condition bracket pairs into a single bracket with logical operators inside',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
[conditionA] || [conditionB]
    page = PAGE
[end]
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
[conditionA || conditionB]
    page = PAGE
[end]
CODE_SAMPLE
                ),
                new CodeSample(
                    <<<'CODE_SAMPLE'
[conditionA] && [conditionB]
    page = PAGE
[end]
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
[conditionA && conditionB]
    page = PAGE
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

        $condition = $this->mergeMultipleBracketConditions($condition);

        if ($condition === $originalCondition) {
            return null;
        }

        $statement->condition = $condition;

        return $statement;
    }

    private function mergeMultipleBracketConditions(string $condition): string
    {
        // Quick check: does it contain the pattern '] ||' or '] &&'?
        if (! preg_match('/\]\s*(\|\||&&)\s*\[/', $condition)) {
            return $condition;
        }

        $expressions = [];
        $operators = [];
        $remaining = trim($condition);

        while ($remaining !== '') {
            if (! preg_match('/^\[([^\]]*)\](.*)$/s', $remaining, $m)) {
                // Unexpected format, bail out without changing anything
                return $condition;
            }

            $expressions[] = $m[1];
            $remaining = trim($m[2]);

            if ($remaining === '') {
                break;
            }

            if (preg_match('/^(\|\||&&)\s*(.*)$/s', $remaining, $opMatch)) {
                $operators[] = $opMatch[1];
                $remaining = trim($opMatch[2]);
            } else {
                // Unexpected token after closing bracket, bail out
                return $condition;
            }
        }

        if (count($expressions) < 2) {
            return $condition;
        }

        $merged = '';
        foreach ($expressions as $i => $expr) {
            if ($i > 0) {
                $merged .= ' ' . $operators[$i - 1] . ' ';
            }

            $merged .= $expr;
        }

        return '[' . $merged . ']';
    }
}
