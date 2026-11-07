<?php

declare(strict_types=1);

namespace a9f\Typo3Fractor\TYPO3v10\TypoScript;

use a9f\Fractor\RuleDoc\CodeSample\CodeSample;
use a9f\Fractor\RuleDoc\RuleDefinition;
use a9f\FractorTypoScript\AbstractTypoScriptFractor;
use Helmich\TypoScriptParser\Parser\AST\ConditionalStatement;
use Helmich\TypoScriptParser\Parser\AST\Statement;

/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/10.0/Breaking-89229-TypoScriptConditionsDeprecatedInV9Removed.html
 * @see \a9f\Typo3Fractor\Tests\TYPO3v10\TypoScript\MigrateLegacyTypoScriptConditionsFractor\MigrateLegacyTypoScriptConditionsFractorTest
 */
final class MigrateLegacyTypoScriptConditionsFractor extends AbstractTypoScriptFractor
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Migrate legacy TypoScript conditions to Symfony expression language syntax', [
            new CodeSample(
                <<<'CODE_SAMPLE'
[globalVar = GP:debug > 0]
    page = PAGE
    page.10 = TEXT
    page.10.value = GET/POST parameter "debug" is greater than 0
[end]
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
[request && traverse(request.getQueryParams(), 'debug') > 0]
    page = PAGE
    page.10 = TEXT
    page.10.value = GET/POST parameter "debug" is greater than 0
[end]
CODE_SAMPLE
            ),
            new CodeSample(
                <<<'CODE_SAMPLE'
[globalVar = GP:tx_news_pi1|news > 0]
    page = PAGE
    page.10 = TEXT
    page.10.value = Nested GET/POST parameter "tx_news_pi1[news]" is set
[end]
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
[request && traverse(request.getQueryParams(), 'tx_news_pi1/news') > 0]
    page = PAGE
    page.10 = TEXT
    page.10.value = Nested GET/POST parameter "tx_news_pi1[news]" is set
[end]
CODE_SAMPLE
            ),
            new CodeSample(
                <<<'CODE_SAMPLE'
[PIDupinRootline = 89,130]
    page = PAGE
    page.10 = TEXT
    page.10.value = A parent page in the rootline has uid 89 or 130
[end]
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
[89 in tree.rootLineParentIds || 130 in tree.rootLineParentIds]
    page = PAGE
    page.10 = TEXT
    page.10.value = A parent page in the rootline has uid 89 or 130
[end]
CODE_SAMPLE
            ),
            new CodeSample(
                <<<'CODE_SAMPLE'
[PIDinRootline = 89,130]
    page = PAGE
    page.10 = TEXT
    page.10.value = Page with uid 89 or 130 is in the rootline
[end]
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
[89 in tree.rootLineIds || 130 in tree.rootLineIds]
    page = PAGE
    page.10 = TEXT
    page.10.value = Page with uid 89 or 130 is in the rootline
[end]
CODE_SAMPLE
            ),
            new CodeSample(
                <<<'CODE_SAMPLE'
[applicationContext = Development]
    page = PAGE
    page.10 = TEXT
    page.10.value = Application context is exactly "Development"
[end]
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
[applicationContext == "Development"]
    page = PAGE
    page.10 = TEXT
    page.10.value = Application context is exactly "Development"
[end]
CODE_SAMPLE
            ),
            new CodeSample(
                <<<'CODE_SAMPLE'
[applicationContext = Development*]
    page = PAGE
    page.10 = TEXT
    page.10.value = Application context starts with "Development"
[end]
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
[like(applicationContext, "Development*")]
    page = PAGE
    page.10 = TEXT
    page.10.value = Application context starts with "Development"
[end]
CODE_SAMPLE
            ),
            new CodeSample(
                <<<'CODE_SAMPLE'
[treeLevel = 1,3]
    page = PAGE
    page.10 = TEXT
    page.10.value = Current page is on tree level 1 or 3
[end]
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
[tree.level in [1,3]]
    page = PAGE
    page.10 = TEXT
    page.10.value = Current page is on tree level 1 or 3
[end]
CODE_SAMPLE
            ),
            new CodeSample(
                <<<'CODE_SAMPLE'
[globalVar = TSFE:beUserLogin = 1]
    page = PAGE
    page.10 = TEXT
    page.10.value = A backend user is logged in on the frontend
[end]
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
[getTSFE().beUserLogin == 1]
    page = PAGE
    page.10 = TEXT
    page.10.value = A backend user is logged in on the frontend
[end]
CODE_SAMPLE
            ),
            new CodeSample(
                <<<'CODE_SAMPLE'
[globalVar = TSFE:id = {$page.home}]
    page = PAGE
    page.10 = TEXT
    page.10.value = Current page uid matches the configured home page
[end]
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
[page["uid"] == {$page.home}]
    page = PAGE
    page.10 = TEXT
    page.10.value = Current page uid matches the configured home page
[end]
CODE_SAMPLE
            ),
            new CodeSample(
                <<<'CODE_SAMPLE'
[globalVar = LIT:1 = {$config.enableFeature}]
    page = PAGE
    page.10 = TEXT
    page.10.value = TypoScript constant equals the literal value
[end]
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
[{$config.enableFeature} == 1]
    page = PAGE
    page.10 = TEXT
    page.10.value = TypoScript constant equals the literal value
[end]
CODE_SAMPLE
            ),
            new CodeSample(
                <<<'CODE_SAMPLE'
[globalVar = BE_USER|user|admin = 1]
    page = PAGE
    page.10 = TEXT
    page.10.value = Current backend user is an admin
[end]
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
[backend.user.isAdmin]
    page = PAGE
    page.10 = TEXT
    page.10.value = Current backend user is an admin
[end]
CODE_SAMPLE
            ),
            new CodeSample(
                <<<'CODE_SAMPLE'
[globalString = IENV:HTTP_HOST = example.org]
    page = PAGE
    page.10 = TEXT
    page.10.value = Current hostname is example.org
[end]
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
[request.getNormalizedParams().getHttpHost() == "example.org"]
    page = PAGE
    page.10 = TEXT
    page.10.value = Current hostname is example.org
[end]
CODE_SAMPLE
            ),
            new CodeSample(
                <<<'CODE_SAMPLE'
[IP = 123.456.*.*]
    page = PAGE
    page.10 = TEXT
    page.10.value = Visitor IP starts with 123.456
[end]
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
[ip("123.456.*.*")]
    page = PAGE
    page.10 = TEXT
    page.10.value = Visitor IP starts with 123.456
[end]
CODE_SAMPLE
            ),
            new CodeSample(
                <<<'CODE_SAMPLE'
[hostname = example.org]
    page = PAGE
    page.10 = TEXT
    page.10.value = Visitor hostname is example.org
[end]
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
[request.getNormalizedParams().getHttpHost() == "example.org"]
    page = PAGE
    page.10 = TEXT
    page.10.value = Visitor hostname is example.org
[end]
CODE_SAMPLE
            ),
            new CodeSample(
                <<<'CODE_SAMPLE'
[compatVersion = 9.5.0]
    page = PAGE
    page.10 = TEXT
    page.10.value = TYPO3 version is at least 9.5.0
[end]
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
[compatVersion("9.5.0")]
    page = PAGE
    page.10 = TEXT
    page.10.value = TYPO3 version is at least 9.5.0
[end]
CODE_SAMPLE
            ),
        ]);
    }

    public function refactor(Statement $statement): ?Statement
    {
        if (! $statement instanceof ConditionalStatement) {
            return null;
        }

        $condition = $statement->condition;
        $originalCondition = $condition;

        $condition = $this->migrateGlobalVarGP($condition);
        $condition = $this->migrateGlobalVarTSFE($condition);
        $condition = $this->migrateGlobalVarLIT($condition);
        $condition = $this->migrateGlobalVarBEUSER($condition);
        $condition = $this->migrateGlobalVarIENV($condition);
        $condition = $this->migratePIDupinRootline($condition);
        $condition = $this->migratePIDinRootline($condition);
        $condition = $this->migrateApplicationContext($condition);
        $condition = $this->migrateTreeLevel($condition);
        $condition = $this->migrateIP($condition);
        $condition = $this->migrateHostname($condition);
        $condition = $this->migrateCompatVersion($condition);

        if ($condition === $originalCondition) {
            return null;
        }

        $statement->condition = $condition;

        return $statement;
    }

    private function migrateGlobalVarGP(string $condition): string
    {
        return (string) preg_replace_callback(
            '/global(?:Var|String)\s*=\s*GP:([^\s><=!]+)\s*([><=!]{1,2})\s*([^\]]*)/',
            static function (array $matches): string {
                $key = str_replace('|', '/', trim($matches[1]));
                $operator = trim($matches[2]);
                $value = trim($matches[3]);

                if ($operator === '=') {
                    $operator = '==';
                }

                if ($value === '') {
                    $value = '""';
                }

                return sprintf(
                    "request && traverse(request.getQueryParams(), '%s') %s %s",
                    $key,
                    $operator,
                    $value
                );
            },
            $condition
        );
    }

    private function migratePIDupinRootline(string $condition): string
    {
        return (string) preg_replace_callback(
            '/PIDupinRootline\s*=\s*([\d,\s{\$\w.}]+)/',
            static function (array $matches): string {
                $ids = array_map(trim(...), explode(',', $matches[1]));

                return implode(
                    ' || ',
                    array_map(static fn (string $id): string => sprintf('%s in tree.rootLineParentIds', $id), $ids)
                );
            },
            $condition
        );
    }

    private function migratePIDinRootline(string $condition): string
    {
        return (string) preg_replace_callback(
            '/PIDinRootline\s*=\s*([\d,\s{\$\w.}]+)/',
            static function (array $matches): string {
                $ids = array_map(trim(...), explode(',', $matches[1]));

                return implode(
                    ' || ',
                    array_map(static fn (string $id): string => sprintf('%s in tree.rootLineIds', $id), $ids)
                );
            },
            $condition
        );
    }

    private function migrateApplicationContext(string $condition): string
    {
        return (string) preg_replace_callback(
            '/applicationContext\s*=(?!=)\s*([^\]]+)/',
            static function (array $matches): string {
                $pattern = trim($matches[1]);

                if (str_contains($pattern, '*')) {
                    return sprintf('like(applicationContext, "%s")', $pattern);
                }

                return sprintf('applicationContext == "%s"', $pattern);
            },
            $condition
        );
    }

    private function migrateGlobalVarTSFE(string $condition): string
    {
        return (string) preg_replace_callback(
            '/global(?:Var|String)\s*=\s*TSFE:([^\s><=!]+)\s*([><=!]{1,2})\s*([^\]]*)/',
            static function (array $matches): string {
                $property = trim($matches[1]);
                $operator = trim($matches[2]);
                $value = trim($matches[3]);

                if ($operator === '=') {
                    $operator = '==';
                }

                if ($value === '') {
                    $value = '""';
                }

                if ($property === 'id') {
                    return sprintf('page["uid"] %s %s', $operator, $value);
                }

                if (str_starts_with($property, 'page|')) {
                    $field = substr($property, 5);
                    return sprintf('page["%s"] %s %s', $field, $operator, $value);
                }

                return sprintf('getTSFE().%s %s %s', str_replace('|', '.', $property), $operator, $value);
            },
            $condition
        );
    }

    private function migrateGlobalVarLIT(string $condition): string
    {
        return (string) preg_replace_callback(
            '/global(?:Var|String)\s*=\s*LIT:([^\s><=!]+)\s*([><=!]{1,2})\s*([^\]]*)/',
            static function (array $matches): string {
                $literal = trim($matches[1]);
                $operator = trim($matches[2]);
                $value = trim($matches[3]);

                if ($operator === '=') {
                    $operator = '==';
                }

                if ($value === '') {
                    $value = '""';
                }

                return sprintf('%s %s %s', $value, $operator, $literal);
            },
            $condition
        );
    }

    private function migrateGlobalVarBEUSER(string $condition): string
    {
        return (string) preg_replace_callback(
            '/global(?:Var|String)\s*=\s*BE_USER\|user\|admin\s*([><=!]{1,2})\s*([^\]]*)/',
            static fn (array $matches): string => 'backend.user.isAdmin',
            $condition
        );
    }

    private function migrateGlobalVarIENV(string $condition): string
    {
        $ienvMapping = [
            'HTTP_HOST' => 'request.getNormalizedParams().getHttpHost()',
            'TYPO3_SSL' => 'request.getNormalizedParams().isHttps()',
            'REQUEST_URI' => 'request.getNormalizedParams().getRequestUri()',
            'SCRIPT_NAME' => 'request.getNormalizedParams().getScriptName()',
            'TYPO3_PORT' => 'request.getNormalizedParams().getRequestPort()',
            'REMOTE_ADDR' => 'request.getAttribute("normalizedParams").getRemoteAddress()',
        ];

        return (string) preg_replace_callback(
            '/global(?:Var|String)\s*=\s*IENV:([^\s><=!]+)\s*([><=!]{1,2})\s*([^\]]*)/',
            static function (array $matches) use ($ienvMapping): string {
                $envVar = trim($matches[1]);
                $operator = trim($matches[2]);
                $value = trim($matches[3]);

                if ($operator === '=') {
                    $operator = '==';
                }

                if ($value === '') {
                    $value = '""';
                }

                $expression = $ienvMapping[$envVar] ?? sprintf('request.getServerParams()["%s"]', $envVar);

                return sprintf('%s %s "%s"', $expression, $operator, $value);
            },
            $condition
        );
    }

    private function migrateTreeLevel(string $condition): string
    {
        return (string) preg_replace_callback(
            '/treeLevel\s*=\s*([\d,\s]+)/',
            static function (array $matches): string {
                $levels = array_map(trim(...), explode(',', $matches[1]));

                if (count($levels) === 1) {
                    return sprintf('tree.level == %s', $levels[0]);
                }

                return sprintf('tree.level in [%s]', implode(',', $levels));
            },
            $condition
        );
    }

    private function migrateIP(string $condition): string
    {
        return (string) preg_replace_callback(
            '/IP\s*=\s*([^\]]+)/',
            static function (array $matches): string {
                $values = array_map(trim(...), explode(',', $matches[1]));

                return implode(
                    ' || ',
                    array_map(static fn (string $ip): string => sprintf('ip("%s")', $ip), $values)
                );
            },
            $condition
        );
    }

    private function migrateHostname(string $condition): string
    {
        return (string) preg_replace_callback(
            '/hostname\s*=\s*([^\]]+)/',
            static function (array $matches): string {
                $values = array_map(trim(...), explode(',', $matches[1]));

                $parts = array_map(static function (string $host): string {
                    if (str_contains($host, '*')) {
                        return sprintf('like(request.getNormalizedParams().getHttpHost(), "%s")', $host);
                    }

                    return sprintf('request.getNormalizedParams().getHttpHost() == "%s"', $host);
                }, $values);

                return implode(' || ', $parts);
            },
            $condition
        );
    }

    private function migrateCompatVersion(string $condition): string
    {
        return (string) preg_replace_callback(
            '/compatVersion\s*=\s*([^\]\s]+)/',
            static fn (array $matches): string => sprintf('compatVersion("%s")', trim($matches[1])),
            $condition
        );
    }
}
