<?php

declare(strict_types=1);

namespace a9f\Fractor\Tests\Reporting\ChangelogExtractor\Fixture;

use a9f\Fractor\Application\Contract\FractorRule;
use a9f\Fractor\RuleDoc\RuleDefinition;

/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/7.6/Deprecation-69822-DeprecateSelectFieldTca.html
 */
final class RuleWithChangelog implements FractorRule
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Foo', []);
    }
}
