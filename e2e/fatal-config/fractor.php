<?php

declare(strict_types=1);

use a9f\Fractor\Configuration\FractorConfiguration;
use a9f\Typo3Fractor\Set\DoesNotExistSetList;

return FractorConfiguration::configure()
    ->withPaths([__DIR__ . '/result/'])
    ->withSets([DoesNotExistSetList::UP_TO_TYPO3_14]);
