<?php

declare(strict_types=1);

namespace a9f\Fractor\Kernel;

use a9f\Fractor\Application\Contract\FractorRule;
use a9f\Fractor\Configuration\FractorConfiguration;
use a9f\Fractor\Configuration\FractorConfigurationBuilder;
use a9f\Fractor\Configuration\Option;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\DependencyInjection\AddConsoleCommandPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Webmozart\Assert\Assert;

final class ContainerBuilderBuilder
{
    /**
     * @param string[] $configFiles
     */
    public function build(?string $fractorConfigFile, array $configFiles): ContainerBuilder
    {
        $containerBuilder = new ContainerBuilder();
        $configurationBuilder = FractorConfiguration::configure();
        try {
            $containerBuilder->set(FractorConfigurationBuilder::class, $configurationBuilder);
            $containerBuilder->addCompilerPass(new AddConsoleCommandPass());
            $containerBuilder->registerForAutoconfiguration(FractorRule::class)->addTag(FractorRule::class);

            // escaped, so that a "%" in the path is not read as a parameter placeholder
            $containerBuilder->setParameter(
                Option::MAIN_CONFIG_FILE,
                $containerBuilder->getParameterBag()
                    ->escapeValue($fractorConfigFile)
            );
            $fractorConfigFile ??= __DIR__ . '/../../config/fractor.php';

            $this->loadFractorConfigFile($fractorConfigFile, $containerBuilder, $configurationBuilder);

            foreach ($configFiles as $configFile) {
                if (! file_exists($configFile)) {
                    continue;
                }

                $fileLoader = new PhpFileLoader($containerBuilder, new FileLocator(dirname($configFile)));
                $fileLoader->load($configFile);
            }

            $containerBuilder->compile();

            return $containerBuilder;
        } finally {
            // reset after we're done building the config
            FractorConfiguration::reset();
        }
    }

    /**
     * The main fractor config file should return a callback that accepts any of the parameters that {@see PhpFileLoader}
     * can inject. This especially means that it can *NOT* accept the {@see FractorConfigurationBuilder} instance directly
     */
    private function loadFractorConfigFile(
        string $fractorConfigFile,
        ContainerBuilder $containerBuilder,
        FractorConfigurationBuilder $builder
    ): void {
        // ensure e.g. relative paths like "build/fractor.php" are correctly resolved by PhpFileLoader
        $absoluteFractorConfigFile = realpath($fractorConfigFile);
        if ($absoluteFractorConfigFile === false) {
            throw new \InvalidArgumentException(sprintf('File %s does not exist', $fractorConfigFile));
        }
        Assert::fileExists($absoluteFractorConfigFile);

        $loader = new PhpFileLoader($containerBuilder, new FileLocator(dirname($absoluteFractorConfigFile)));
        $loader->load($absoluteFractorConfigFile);

        $instanceOf = [];
        $builder(new ContainerConfigurator(
            $containerBuilder,
            $loader,
            $instanceOf,
            dirname($absoluteFractorConfigFile),
            basename($absoluteFractorConfigFile)
        ));
    }
}
