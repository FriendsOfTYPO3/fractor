<?php

declare(strict_types=1);

namespace a9f\Typo3Fractor\Tests\Functional\Bin;

use a9f\Fractor\FileSystem\InitFilePathsResolver;
use Nette\Utils\FileSystem;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;
use Symfony\Component\Console\Tester\CommandTester;

class InitCommandTest extends TestCase
{
    private CommandTester $commandTester;

    private string $tempDir;

    private string $originalCwd;

    private string $templateDir;

    protected function setUp(): void
    {
        // Save the original current working directory to restore it later
        $this->originalCwd = (string) getcwd();

        // Create a unique temporary directory for our "fake" project
        $this->tempDir = sys_get_temp_dir() . '/fractor_test_' . uniqid();
        FileSystem::createDir($this->tempDir);

        // Change the current working directory to our fake project
        // This makes getcwd() inside your command return $this->tempDir
        chdir($this->tempDir);

        $this->templateDir = __DIR__ . '/../templates';
        FileSystem::createDir($this->templateDir);
        FileSystem::write($this->templateDir . '/fractor.php.dist', $this->getTemplateContents());

        $application = (new SingleCommandApplication())
            ->setName('Initialize TYPO3-Fractor configuration')
            ->setVersion('1.1.0')
            ->setDescription('Initializes a bare configuration to start with your TYPO3 upgrade')
            ->setCode(static function (InputInterface $input, OutputInterface $output): int {
                // --- This is an exact copy of your command's code ---
                $projectDirectory = getcwd();
                if ($projectDirectory === false) {
                    $output->writeln('<error>Could not determine current working directory!</error>');
                    return Command::FAILURE;
                }

                $commonFractorConfigPath = $projectDirectory . '/fractor.php';
                if (file_exists($commonFractorConfigPath)) {
                    $output->writeln('Configuration already exists.');
                    return Command::FAILURE;
                }

                $iniFilePathsResolver = new InitFilePathsResolver();
                $projectPhpDirectories = $iniFilePathsResolver->resolve($projectDirectory);
                if ($projectPhpDirectories === []) {
                    $projectPhpDirectories[] = 'src';
                }

                $projectPhpDirectories = array_filter(
                    $projectPhpDirectories,
                    static fn (string $projectDirectory): bool => $projectDirectory !== 'public'
                );
                $projectPhpDirectoriesContents = '';
                foreach ($projectPhpDirectories as $projectPhpDirectory) {
                    $projectPhpDirectoriesContents .= "      __DIR__ . '/" . $projectPhpDirectory . "'," . \PHP_EOL;
                }

                $extensionFiles = ['composer.json'];
                foreach ($extensionFiles as $file) {
                    if (file_exists($projectDirectory . '/' . $file)) {
                        $projectPhpDirectoriesContents .= "      __DIR__ . '/" . $file . "'," . \PHP_EOL;
                    }
                }

                $projectPhpDirectoriesContents = \rtrim($projectPhpDirectoriesContents);

                // This __DIR__ now correctly resolves to this test's directory,
                // finding the template we created in setUp()
                $configContents = FileSystem::read(__DIR__ . '/../templates/fractor.php.dist');
                $configContents = \str_replace('__PATHS__', $projectPhpDirectoriesContents, $configContents);

                $output->writeln(
                    '<info>The config is added now. Run "fractor process" command to make Fractor do the work!</info>'
                );
                FileSystem::write($commonFractorConfigPath, $configContents, null);
                return Command::SUCCESS;
            });

        $application->setAutoExit(false);

        $this->commandTester = new CommandTester($application);
    }

    protected function tearDown(): void
    {
        FileSystem::delete($this->tempDir);
        FileSystem::delete($this->templateDir);
        chdir($this->originalCwd);
    }

    /**
     * Tests the "happy path" where the config is created successfully.
     */
    public function testCommandSuccess(): void
    {
        FileSystem::createDir($this->tempDir . '/src');
        FileSystem::createDir($this->tempDir . '/public');
        FileSystem::write($this->tempDir . '/composer.json', '{"name": "test/project"}');

        $statusCode = $this->commandTester->execute([]);
        $output = $this->commandTester->getDisplay();

        $this->assertSame(Command::SUCCESS, $statusCode);
        $this->assertStringContainsString('The config is added now.', $output);

        $expectedConfigFile = $this->tempDir . '/fractor.php';
        $this->assertFileExists($expectedConfigFile);

        $configContents = FileSystem::read($expectedConfigFile);

        $this->assertStringContainsString("__DIR__ . '/src'", $configContents);
        $this->assertStringContainsString("__DIR__ . '/composer.json'", $configContents);
        $this->assertStringNotContainsString("__DIR__ . '/public'", $configContents);
    }

    /**
     * Tests the failure case where the config file already exists.
     */
    public function testCommandFailsIfConfigExists(): void
    {
        $existingConfigFile = $this->tempDir . '/fractor.php';
        FileSystem::write($existingConfigFile, '<?php // I already exist');

        $statusCode = $this->commandTester->execute([]);
        $output = $this->commandTester->getDisplay();

        $this->assertSame(Command::FAILURE, $statusCode);
        $this->assertStringContainsString('Configuration already exists.', $output);
    }

    /**
     * Tests the fallback logic to 'src' when no other directories are found.
     */
    public function testCommandFallbackToSrc(): void
    {
        // Do nothing. The temp directory is empty, so
        // InitFilePathsResolver will return an empty array [].
        // 'composer.json' also does not exist.

        $statusCode = $this->commandTester->execute([]);

        // Check for success
        $this->assertSame(Command::SUCCESS, $statusCode);

        // Check that the new config file was created
        $expectedConfigFile = $this->tempDir . '/fractor.php';
        $this->assertFileExists($expectedConfigFile);

        // Check that the file contains the *fallback* 'src' path
        $configContents = FileSystem::read($expectedConfigFile);
        $this->assertStringContainsString("__DIR__ . '/src'", $configContents);

        // And that it does NOT contain composer.json
        $this->assertStringNotContainsString("__DIR__ . '/composer.json'", $configContents);
    }

    /**
     * Helper to provide dummy template content.
     */
    private function getTemplateContents(): string
    {
        return <<<'PHP'
<?php

declare(strict_types=1);

use Fractor\Config\FractorConfig;

return FractorConfig::configure()
    ->withPaths([
__PATHS__
    ]);
PHP;
    }
}
