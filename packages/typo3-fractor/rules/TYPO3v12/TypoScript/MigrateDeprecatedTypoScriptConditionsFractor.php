<?php

declare(strict_types=1);

namespace a9f\Typo3Fractor\TYPO3v12\TypoScript;

use a9f\Fractor\RuleDoc\CodeSample\CodeSample;
use a9f\Fractor\RuleDoc\RuleDefinition;
use a9f\FractorTypoScript\AbstractTypoScriptFractor;
use Helmich\TypoScriptParser\Parser\AST\ConditionalStatement;
use Helmich\TypoScriptParser\Parser\AST\Statement;

/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/12.4/Deprecation-100405-PropertyTypoScriptFrontendController-type.html
 * @see \a9f\Typo3Fractor\Tests\TYPO3v12\TypoScript\MigrateDeprecatedTypoScriptConditionsFractor\MigrateDeprecatedTypoScriptConditionsFractorTest
 */
final class MigrateDeprecatedTypoScriptConditionsFractor extends AbstractTypoScriptFractor
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Migrate deprecated globalVar TSFE conditions to v12 syntax',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
[globalVar = TSFE:type = 9818]
    page = PAGE
[end]
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
[request.getPageArguments()?.getPageType() == '9818']
    page = PAGE
[end]
CODE_SAMPLE
                ),
                new CodeSample(
                    <<<'CODE_SAMPLE'
[globalVar = TSFE:beUserLogin > 0]
    page = PAGE
[end]
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
[getTSFE()?.isBackendUserLoggedIn()]
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

        $condition = $this->migrateGlobalVarTSFEType($condition);
        $condition = $this->migrateGlobalVarTSFEBeUserLogin($condition);

        if ($condition === $originalCondition) {
            return null;
        }

        $statement->condition = $condition;

        return $statement;
    }

    private function migrateGlobalVarTSFEType(string $condition): string
    {
        return (string) preg_replace_callback(
            '/globalVar\s*=\s*TSFE:type\s*=\s*([^\]\s]+)/',
            static fn (array $matches): string => sprintf(
                "request.getPageArguments()?.getPageType() == '%s'",
                trim($matches[1])
            ),
            $condition
        );
    }

    private function migrateGlobalVarTSFEBeUserLogin(string $condition): string
    {
        return (string) preg_replace(
            '/globalVar\s*=\s*TSFE:beUserLogin\s*>\s*0/',
            'getTSFE()?.isBackendUserLoggedIn()',
            $condition
        );
    }
}
