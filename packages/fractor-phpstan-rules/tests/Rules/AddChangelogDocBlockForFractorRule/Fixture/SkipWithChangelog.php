<?php

declare(strict_types=1);

namespace a9f\FractorPhpStanRules\Tests\Rules\AddChangelogDocBlockForFractorRule\Fixture;

use a9f\Fractor\Application\Contract\FractorRule;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @changelog https://github.com/friendsoftypo3/fractor
 */
final class SkipWithChangelog implements FractorRule
{
    public function getRuleDefinition(): RuleDefinition
    {
        throw new \BadMethodCallException('Not implemented yet');
    }
}
