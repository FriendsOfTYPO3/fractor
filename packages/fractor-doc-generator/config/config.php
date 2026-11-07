<?php

declare(strict_types=1);

use a9f\FractorDocGenerator\Console\Factory\SymfonyStyleFactory;
use a9f\FractorDocGenerator\Contract\RuleCodeSamplePrinterInterface;
use a9f\FractorDocGenerator\Differ\DifferFactory;
use a9f\FractorDocGenerator\FractorDocGeneratorApplication;
use a9f\FractorDocGenerator\Printer\CodeSamplePrinter;
use SebastianBergmann\Diff\Differ;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $containerConfigurator, ContainerBuilder $containerBuilder): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->public()
        ->autoconfigure();

    $services->load('a9f\\FractorDocGenerator\\', __DIR__ . '/../src/')
        ->exclude([__DIR__ . '/../src/ValueObject']);
    $services->set(Differ::class)->factory([service(DifferFactory::class), 'create']);

    $services->set(FractorDocGeneratorApplication::class)
        ->call('setCommandLoader', [service('console.command_loader')])
        ->public();

    $services->set(SymfonyStyle::class)->factory([service(SymfonyStyleFactory::class), 'create']);

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

    $services->set(CodeSamplePrinter::class)->arg(
        '$ruleCodeSamplePrinters',
        tagged_iterator('fractor_doc_generator.rule_code_sample_printer')
    );

    $containerBuilder->registerForAutoconfiguration(RuleCodeSamplePrinterInterface::class)->addTag(
        'fractor_doc_generator.rule_code_sample_printer'
    );
};
