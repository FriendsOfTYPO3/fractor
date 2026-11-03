<?php

declare(strict_types=1);

namespace a9f\Fractor\Tests\Configuration\Parameter;

use a9f\Fractor\Configuration\Option;
use a9f\Fractor\Configuration\Parameter\SimpleParameterProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SimpleParameterProviderTest extends TestCase
{
    /**
     * @var mixed[]
     */
    private array $originalPaths;

    /**
     * @var mixed[]
     */
    private array $originalSkip;

    protected function setUp(): void
    {
        $this->originalPaths = SimpleParameterProvider::provideArrayParameter(Option::PATHS);
        $this->originalSkip = SimpleParameterProvider::provideArrayParameter(Option::SKIP);
    }

    protected function tearDown(): void
    {
        SimpleParameterProvider::setParameter(Option::PATHS, $this->originalPaths);
        SimpleParameterProvider::setParameter(Option::SKIP, $this->originalSkip);
    }

    #[Test]
    public function sharedConfigForDifferentExtensionsKeepsTheSameHash(): void
    {
        SimpleParameterProvider::setParameter(Option::PATHS, ['/var/www/html/packages/ext_a/**/*']);
        $hashForExtensionA = SimpleParameterProvider::hash();

        SimpleParameterProvider::setParameter(Option::PATHS, ['/var/www/html/packages/ext_b/**/*']);

        self::assertSame($hashForExtensionA, SimpleParameterProvider::hash());
    }

    #[Test]
    public function changedSkipChangesTheHash(): void
    {
        SimpleParameterProvider::setParameter(Option::SKIP, ['*/vendor/*']);
        $hash = SimpleParameterProvider::hash();

        SimpleParameterProvider::setParameter(Option::SKIP, ['*/vendor/*', '*/Documentation/*']);

        self::assertNotSame($hash, SimpleParameterProvider::hash());
    }
}
