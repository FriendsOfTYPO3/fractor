<?php

declare(strict_types=1);

namespace a9f\Fractor\Tests\Kernel\ContainerBuilderBuilder;

use a9f\Fractor\Configuration\Option;
use a9f\Fractor\Kernel\ContainerBuilderBuilder;
use Nette\Utils\FileSystem;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ContainerBuilderBuilderTest extends TestCase
{
    private const APPLICATION_CONFIG = __DIR__ . '/../../../config/application.php';

    public function testMainConfigFileIsTheGivenConfig(): void
    {
        $mainConfigFile = __DIR__ . '/config/fractor.php';

        $containerBuilder = (new ContainerBuilderBuilder())->build($mainConfigFile, [self::APPLICATION_CONFIG]);

        self::assertSame($mainConfigFile, $containerBuilder->getParameter(Option::MAIN_CONFIG_FILE));
    }

    /**
     * @return \Iterator<string, array{string}>
     */
    public static function providePercentDirectoryNames(): \Iterator
    {
        yield 'placeholder' => ['with-%placeholder%-in-name'];
        yield 'escaped percent' => ['with-%%-in-name'];
    }

    #[DataProvider('providePercentDirectoryNames')]
    public function testMainConfigFileInDirectoryWithPercentIsKeptLiterally(string $directoryName): void
    {
        $directory = sys_get_temp_dir() . '/' . bin2hex(random_bytes(8)) . '-' . $directoryName;
        $mainConfigFile = $directory . '/fractor.php';
        FileSystem::write(
            $mainConfigFile,
            "<?php\n\nreturn \\a9f\\Fractor\\Configuration\\FractorConfiguration::configure();\n"
        );

        try {
            $containerBuilder = (new ContainerBuilderBuilder())->build($mainConfigFile, [self::APPLICATION_CONFIG]);

            self::assertSame($mainConfigFile, $containerBuilder->getParameter(Option::MAIN_CONFIG_FILE));
        } finally {
            FileSystem::delete($directory);
        }
    }

    public function testMainConfigFileIsNullWithoutConfig(): void
    {
        // without a given config, the builder falls back to Fractor's internal default config, which is not the user's
        $containerBuilder = (new ContainerBuilderBuilder())->build(null, [self::APPLICATION_CONFIG]);

        self::assertNull($containerBuilder->getParameter(Option::MAIN_CONFIG_FILE));
    }
}
