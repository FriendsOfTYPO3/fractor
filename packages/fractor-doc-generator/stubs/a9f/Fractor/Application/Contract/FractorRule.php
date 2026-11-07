<?php

declare(strict_types=1);

namespace a9f\Fractor\Application\Contract;

use a9f\Fractor\RuleDoc\Contract\DocumentedRuleInterface;

if (interface_exists(FractorRule::class)) {
    return;
}

interface FractorRule extends DocumentedRuleInterface
{
}
