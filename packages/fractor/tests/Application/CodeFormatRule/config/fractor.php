<?php

declare(strict_types=1);

use a9f\Fractor\Configuration\FractorConfiguration;
use a9f\Fractor\Tests\Fixture\DummyProcessor\Rules\NormalizeWhitespaceTextRule;

return FractorConfiguration::configure()
    ->withRules([NormalizeWhitespaceTextRule::class]);
