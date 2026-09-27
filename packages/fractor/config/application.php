<?php

declare(strict_types=1);

use a9f\Fractor\Application\Contract\FileProcessor;
use a9f\Fractor\Application\Contract\FractorRule;
use a9f\Fractor\Application\FractorRunner;
use a9f\Fractor\Caching\Cache;
use a9f\Fractor\Caching\CacheFactory;
use a9f\Fractor\ChangesReporting\Contract\Output\OutputFormatterInterface;
use a9f\Fractor\Configuration\AllowedFileExtensionsResolver;
use a9f\Fractor\Configuration\ConfigInitializer;
use a9f\Fractor\Configuration\ConfigurationRuleFilter;
use a9f\Fractor\Configuration\OnlyRuleResolver;
use a9f\Fractor\Configuration\Option;
use a9f\Fractor\Configuration\SkipConfigurationFactory;
use a9f\Fractor\Configuration\ValueObject\SkipConfiguration;
use a9f\Fractor\Console\Application\FractorApplication;
use a9f\Fractor\Console\Output\OutputFormatterCollector;
use a9f\Fractor\Console\Style\FractorStyle;
use a9f\Fractor\Console\Style\SymfonyStyleFactory;
use a9f\Fractor\Contract\FilesystemInterface;
use a9f\Fractor\Contract\LocalFilesystemInterface;
use a9f\Fractor\FileSystem\FilesystemFactory;
use a9f\Fractor\FileSystem\FlysystemFilesystem;
use League\Flysystem\FilesystemAdapter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBag;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $containerConfigurator, ContainerBuilder $containerBuilder): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->public()
        ->autoconfigure();

    $services->load('a9f\\Fractor\\', __DIR__ . '/../src/')
        ->exclude(
            [
                __DIR__ . '/../src/Console/Output',
                __DIR__ . '/../src/Testing',
                __DIR__ . '/../src/ValueObject',
                __DIR__ . '/../src/**/ValueObject',
            ]
        );

    $containerBuilder->registerAttributeForAutoconfiguration(
        AsCommand::class,
        static function (ChildDefinition $definition, AsCommand $attribute): void {
            $commands = explode('|', $attribute->name);
            $hidden = false;
            $name = array_shift($commands);

            if ($name === '') {
                // Symfony AsCommand attribute encodes hidden flag as an empty command name
                $hidden = true;
                $name = array_shift($commands);
            }

            if ($name === null) {
                // This happens in case no name and no aliases are given
                return;
            }

            $definition->addTag(
                'console.command',
                [
                    'command' => $name,
                    'description' => $attribute->description,
                    'hidden' => $hidden,
                ]
            );

            foreach ($commands as $name) {
                $definition->addTag(
                    'console.command',
                    [
                        'command' => $name,
                        'hidden' => $hidden,
                        'alias' => true,
                    ]
                );
            }
        }
    );

    $services->set(\League\Flysystem\Filesystem::class)->public();
    $services->set(FilesystemAdapter::class)->public();

    $services->set(FilesystemFactory::class)
        ->arg('$projectDir', '/');
    $services->set(LocalFilesystemInterface::class)
        ->factory([service(FilesystemFactory::class), 'createLocalFilesystem']);
    $services->set(FilesystemInterface::class)
        ->factory([service(FilesystemFactory::class), 'create']);
    $services->set(FlysystemFilesystem::class)
        ->arg('$filesystemOperator', service(\League\Flysystem\Filesystem::class));

    $services->set('parameter_bag', ContainerBag::class)
        ->args([service('service_container')])
        ->alias(ContainerBagInterface::class, 'parameter_bag')
        ->alias(ParameterBagInterface::class, 'parameter_bag');

    $services->set(FractorApplication::class)->call('setCommandLoader', [service('console.command_loader')]);
    $services->set(SkipConfiguration::class)->factory([service(SkipConfigurationFactory::class), 'create']);
    $services->set(FractorRunner::class)->arg('$processors', tagged_iterator('fractor.file_processor'));
    $services->set(AllowedFileExtensionsResolver::class)->arg('$processors', tagged_iterator('fractor.file_processor'));
    $services->set(OutputFormatterCollector::class)->arg(
        '$outputFormatters',
        tagged_iterator('fractor.output_formatter')
    );
    $services->set(Filesystem::class);
    $services->set(ConfigurationRuleFilter::class);

    // console
    $services->set(SymfonyStyleFactory::class);

    $services->alias(FractorStyle::class, SymfonyStyle::class);
    $services->set(SymfonyStyle::class)
        ->factory([service(SymfonyStyleFactory::class), 'create']);

    // cache
    $services->set(Cache::class)->factory([service(CacheFactory::class), 'create']);

    // tagged services
    $services->set(ConfigInitializer::class)
        ->arg('$fractors', tagged_iterator(FractorRule::class))
        ->arg('$mainConfigFile', param(Option::MAIN_CONFIG_FILE));

    $services->set(OnlyRuleResolver::class)
        ->arg('$fractors', tagged_iterator(FractorRule::class));

    $containerBuilder->registerForAutoconfiguration(FileProcessor::class)->addTag('fractor.file_processor');
    $containerBuilder->registerForAutoconfiguration(OutputFormatterInterface::class)->addTag(
        'fractor.output_formatter'
    );
};
