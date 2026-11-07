<?php

declare(strict_types=1);

namespace a9f\Fractor\Tests\Application\RuleSkipper\Fixture;

use a9f\Fractor\Application\Contract\FractorRule;
use a9f\Fractor\RuleDoc\RuleDefinition;

final class RuleA implements FractorRule
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('', []);
    }
}
