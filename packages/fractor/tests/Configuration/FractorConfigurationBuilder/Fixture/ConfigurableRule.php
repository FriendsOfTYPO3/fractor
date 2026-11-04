<?php

declare(strict_types=1);

namespace a9f\Fractor\Tests\Configuration\FractorConfigurationBuilder\Fixture;

use a9f\Fractor\Application\Contract\ConfigurableFractorRule;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class ConfigurableRule implements ConfigurableFractorRule
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('', []);
    }

    public function configure(array $configuration): void
    {
    }
}
