<?php

declare(strict_types=1);

use a9f\Typo3Fractor\TYPO3v13\TypoScript\MigrateIncludeTypoScriptSyntaxFractor;
use a9f\Typo3Fractor\TYPO3v13\TypoScript\RemovePageDoktypeRecyclerFromUserTsConfigFractor;
use a9f\Typo3Fractor\TYPO3v13\TypoScript\MigratePluginContentElementAndPluginSubtypesFractor;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    $services->set(MigrateIncludeTypoScriptSyntaxFractor::class);
    $services->set(RemovePageDoktypeRecyclerFromUserTsConfigFractor::class);
    $services->set(MigratePluginContentElementAndPluginSubtypesFractor::class);
};
