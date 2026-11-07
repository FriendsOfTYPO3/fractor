<?php

declare(strict_types=1);

namespace a9f\FractorPhpStanRules\Tests\Rules\AddChangelogDocBlockForFractorRule\Fixture;

use a9f\Fractor\Application\Contract\FractorRule;
use a9f\Fractor\RuleDoc\RuleDefinition;

/**
 * @changelog https://github.com/andreaswolf/fractor
 */
final class SkipWithChangelog implements FractorRule
{
    public function getRuleDefinition(): RuleDefinition
    {
        throw new \BadMethodCallException('Not implemented yet');
    }
}
