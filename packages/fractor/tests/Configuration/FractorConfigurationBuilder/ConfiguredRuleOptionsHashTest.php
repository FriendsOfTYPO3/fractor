<?php

declare(strict_types=1);

namespace a9f\Fractor\Tests\Configuration\FractorConfigurationBuilder;

use a9f\Fractor\Configuration\FractorConfigurationBuilder;
use a9f\Fractor\Configuration\Option;
use a9f\Fractor\Configuration\Parameter\SimpleParameterProvider;
use a9f\Fractor\Tests\Configuration\FractorConfigurationBuilder\Fixture\ConfigurableRule;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

final class ConfiguredRuleOptionsHashTest extends TestCase
{
    /**
     * @var mixed[]
     */
    private array $originalRegisteredRules;

    /**
     * @var mixed[]
     */
    private array $originalConfiguredRules;

    protected function setUp(): void
    {
        $this->originalRegisteredRules = SimpleParameterProvider::provideArrayParameter(
            Option::REGISTERED_FRACTOR_RULES
        );
        $this->originalConfiguredRules = SimpleParameterProvider::provideArrayParameter(
            Option::CONFIGURED_FRACTOR_RULES
        );
    }

    protected function tearDown(): void
    {
        SimpleParameterProvider::setParameter(Option::REGISTERED_FRACTOR_RULES, $this->originalRegisteredRules);
        SimpleParameterProvider::setParameter(Option::CONFIGURED_FRACTOR_RULES, $this->originalConfiguredRules);
    }

    #[Test]
    public function changedOptionsOfConfiguredRuleChangeTheHash(): void
    {
        $this->applyConfiguredRule([
            'from' => 'nomatch',
        ]);
        $hash = SimpleParameterProvider::hash();

        $this->applyConfiguredRule([
            'from' => 'foo',
        ]);

        self::assertNotSame($hash, SimpleParameterProvider::hash());
    }

    #[Test]
    public function sameOptionsOfConfiguredRuleKeepTheHash(): void
    {
        $this->applyConfiguredRule([
            'from' => 'foo',
        ]);
        $hash = SimpleParameterProvider::hash();

        $this->applyConfiguredRule([
            'from' => 'foo',
        ]);

        self::assertSame($hash, SimpleParameterProvider::hash());
    }

    /**
     * @param array<string, string> $configuration
     */
    private function applyConfiguredRule(array $configuration): void
    {
        // every run starts in a fresh process, with no rules registered yet
        SimpleParameterProvider::setParameter(Option::REGISTERED_FRACTOR_RULES, []);
        SimpleParameterProvider::setParameter(Option::CONFIGURED_FRACTOR_RULES, []);

        $containerBuilder = new ContainerBuilder();
        $instanceof = [];
        $containerConfigurator = new ContainerConfigurator(
            $containerBuilder,
            new PhpFileLoader($containerBuilder, new FileLocator()),
            $instanceof,
            __DIR__,
            __FILE__
        );

        (new FractorConfigurationBuilder())
            ->withConfiguredRule(ConfigurableRule::class, $configuration)
            ->__invoke($containerConfigurator);
    }
}
