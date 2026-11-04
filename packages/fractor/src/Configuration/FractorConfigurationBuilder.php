<?php

declare(strict_types=1);

namespace a9f\Fractor\Configuration;

use a9f\Fractor\Application\Contract\ConfigurableFractorRule;
use a9f\Fractor\Application\Contract\FractorRule;
use a9f\Fractor\Caching\Contract\ValueObject\Storage\CacheStorageInterface;
use a9f\Fractor\Caching\ValueObject\Storage\MemoryCacheStorage;
use a9f\Fractor\Configuration\Parameter\SimpleParameterProvider;
use a9f\Fractor\Configuration\ValueObject\SkipConfiguration;
use a9f\Fractor\Testing\PHPUnit\StaticPHPUnitEnvironment;
use Helmich\TypoScriptParser\Parser\Printer\PrettyPrinterConditionTermination;
use OndraM\CiDetector\CiDetector;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Webmozart\Assert\Assert;

/**
 * The configuration builder collects configuration from the configuration file and its possible imports.
 *
 * An instance of the builder can be obtained via {@see FractorConfiguration::configure()} or from an instance
 * of {@see ContainerBuilder} passed to the callable returned by fractor.php.
 *
 * @phpstan-import-type TSkipConfiguration from SkipConfiguration
 */
final class FractorConfigurationBuilder
{
    /**
     * @var string[]
     */
    private array $sets = [];

    /**
     * @var string[]
     */
    private array $paths = [];

    /**
     * @var TSkipConfiguration
     */
    private array $skip = [];

    /**
     * @var array<class-string<FractorRule>>
     */
    private array $rules = [];

    /**
     * @var array<class-string<ConfigurableFractorRule>, array<int|string, mixed>>
     */
    private array $rulesWithConfigurations = [];

    /**
     * @var string[]
     */
    private array $imports = [];

    /**
     * @var array<string, string|int|bool|PrettyPrinterConditionTermination>
     */
    private array $options = [];

    /**
     * @var null|class-string<CacheStorageInterface>
     */
    private ?string $cacheClass = null;

    private ?string $cacheDirectory = null;

    private ?string $containerCacheDirectory = null;

    private bool $noDiffs = false;

    public function __invoke(ContainerConfigurator $containerConfigurator): void
    {
        foreach ($this->imports as $import) {
            $containerConfigurator->import($import);
        }

        Assert::allString($this->paths);

        $parameters = $containerConfigurator->parameters();
        $parameters->set(Option::PATHS, $this->paths);
        SimpleParameterProvider::setParameter(Option::PATHS, $this->paths);

        $parameters->set(Option::SKIP, $this->skip);
        SimpleParameterProvider::addParameter(Option::SKIP, $this->skip);

        $parameters->set(Option::CACHE_DIR, $this->cacheDirectory ?? \sys_get_temp_dir() . '/fractor_cached_files');
        SimpleParameterProvider::setParameter(
            Option::CACHE_DIR,
            $this->cacheDirectory ?? \sys_get_temp_dir() . '/fractor_cached_files'
        );

        if ($this->cacheClass !== null) {
            $parameters->set(Option::CACHE_CLASS, $this->cacheClass);
            SimpleParameterProvider::setParameter(Option::CACHE_CLASS, $this->cacheClass);
        }
        if (StaticPHPUnitEnvironment::isPHPUnitRun() || (new CiDetector())->isCiDetected()) {
            $parameters->set(Option::CACHE_CLASS, MemoryCacheStorage::class);
            SimpleParameterProvider::setParameter(Option::CACHE_CLASS, MemoryCacheStorage::class);
        }

        $parameters->set(Option::CONTAINER_CACHE_DIRECTORY, $this->containerCacheDirectory);
        SimpleParameterProvider::setParameter(Option::CONTAINER_CACHE_DIRECTORY, $this->containerCacheDirectory);

        $parameters->set(Option::NO_DIFFS, $this->noDiffs);
        SimpleParameterProvider::setParameter(Option::NO_DIFFS, $this->noDiffs);

        foreach ($this->options as $optionName => $optionValue) {
            $parameters->set($optionName, $optionValue);
        }
        SimpleParameterProvider::setParameter(Option::OPTIONS, $this->options);

        $services = $containerConfigurator->services();

        foreach ($this->rules as $rule) {
            Assert::classExists($rule);
            Assert::isAOf($rule, FractorRule::class);
            $services->set($rule)
                ->autoconfigure()
                ->autowire();

            // for cache invalidation in case of change
            SimpleParameterProvider::addParameter(Option::REGISTERED_FRACTOR_RULES, $rule);
        }

        Assert::allString($this->sets);
        foreach ($this->sets as $set) {
            Assert::fileExists($set);
            $containerConfigurator->import($set);
        }

        foreach ($this->rulesWithConfigurations as $configuredRule => $configuration) {
            Assert::classExists($configuredRule);
            Assert::isAOf($configuredRule, ConfigurableFractorRule::class);

            // for cache invalidation in case of changed rule options
            SimpleParameterProvider::addParameter(
                Option::CONFIGURED_FRACTOR_RULES,
                [
                    $configuredRule => $configuration,
                ]
            );

            // decorate with value object inliner so Symfony understands, see https://getrector.org/blog/2020/09/07/how-to-inline-value-object-in-symfony-php-config
            array_walk_recursive($configuration, static function (&$value) {
                if (is_object($value)) {
                    $value = ValueObjectInliner::inline($value);
                }

                return $value;
            });

            $services->set($configuredRule)
                ->call('configure', $configuration)
                ->autoconfigure()
                ->autowire();

            // for cache invalidation in case of change
            SimpleParameterProvider::addParameter(Option::REGISTERED_FRACTOR_RULES, $configuredRule);
        }
    }

    /**
     * @param string[] $paths
     */
    public function withPaths(array $paths): self
    {
        $this->paths = $paths;

        return $this;
    }

    /**
     * @param TSkipConfiguration $skip
     */
    public function withSkip(array $skip): self
    {
        $this->skip = array_merge($this->skip, $skip);

        return $this;
    }

    /**
     * @param string[] $sets
     */
    public function withSets(array $sets): self
    {
        $this->sets = array_merge($this->sets, $sets);

        return $this;
    }

    /**
     * @param class-string<ConfigurableFractorRule> $fractorClass
     * @param array<int|string, mixed> $configuration
     */
    public function withConfiguredRule(string $fractorClass, array $configuration): self
    {
        $this->rulesWithConfigurations[$fractorClass][] = $configuration;

        return $this;
    }

    /**
     * @param array<class-string<FractorRule>> $rules
     */
    public function withRules(array $rules): self
    {
        $this->rules = array_merge($this->rules, $rules);

        return $this;
    }

    public function import(string $import): self
    {
        $this->imports[] = $import;

        return $this;
    }

    public function withNoDiffs(): self
    {
        $this->noDiffs = true;

        return $this;
    }

    /**
     * @param array<string, string|int|bool|PrettyPrinterConditionTermination> $options
     */
    public function withOptions(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @param null|class-string<CacheStorageInterface> $cacheClass
     */
    public function withCache(
        ?string $cacheDirectory = null,
        ?string $cacheClass = null,
        ?string $containerCacheDirectory = null
    ): self {
        $this->cacheDirectory = $cacheDirectory;
        $this->cacheClass = $cacheClass;
        $this->containerCacheDirectory = $containerCacheDirectory;
        return $this;
    }
}
