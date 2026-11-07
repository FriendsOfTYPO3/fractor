<?php

declare(strict_types=1);

namespace a9f\FractorPhpStanRules\Tests\Rules\AddChangelogDocBlockForFractorRule\Fixture;

use a9f\Fractor\Application\Contract\FractorRule;
use a9f\Fractor\RuleDoc\RuleDefinition;

final class MissingChangelog implements FractorRule
{
    public function getRuleDefinition(): RuleDefinition
    {
        throw new \BadMethodCallException('Not implemented yet');
    }
}
