<?php

declare(strict_types=1);

use a9f\Fractor\Configuration\FractorConfiguration;
use a9f\FractorComposerJson\RemovePackageComposerJsonFractor;

return FractorConfiguration::configure()
    ->import(__DIR__ . '/../../../config/application.php')
    ->withConfiguredRule(RemovePackageComposerJsonFractor::class, ['vendor1/legacy']);
