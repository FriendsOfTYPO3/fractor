<?php

declare(strict_types=1);

namespace a9f\Fractor\RuleDoc\Contract;

use a9f\Fractor\RuleDoc\RuleDefinition;

interface DocumentedRuleInterface
{
    public function getRuleDefinition(): RuleDefinition;
}
