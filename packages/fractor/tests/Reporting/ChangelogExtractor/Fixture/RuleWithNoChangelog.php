<?php

declare(strict_types=1);

namespace a9f\Fractor\Tests\Reporting\ChangelogExtractor\Fixture;

use a9f\Fractor\Application\Contract\FractorRule;
use a9f\Fractor\RuleDoc\RuleDefinition;

final class RuleWithNoChangelog implements FractorRule
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Foo', []);
    }
}
