<?php

declare(strict_types=1);

namespace a9f\Fractor\Tests\Configuration\ConfigInitializer;

use a9f\Fractor\Configuration\ConfigInitializer;
use a9f\Fractor\Testing\PHPUnit\AbstractFractorTestCase;

final class ConfigInitializerWiringTest extends AbstractFractorTestCase
{
    public function test(): void
    {
        $configInitializer = $this->getService(ConfigInitializer::class);

        // the service writes nothing either way when the input is no terminal, and its output is silenced in tests,
        // so the injected value itself is the only thing that tells whether the container passes the config on
        $mainConfigFile = (new \ReflectionProperty(ConfigInitializer::class, 'mainConfigFile'))->getValue(
            $configInitializer
        );

        self::assertSame($this->provideConfigFilePath(), $mainConfigFile);
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/fractor.php';
    }
}
