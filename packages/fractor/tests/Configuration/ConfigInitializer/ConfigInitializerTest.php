<?php

declare(strict_types=1);

namespace a9f\Fractor\Tests\Configuration\ConfigInitializer;

use a9f\Fractor\Configuration\ConfigInitializer;
use a9f\Fractor\Console\TerminalDetector;
use a9f\Fractor\FileSystem\InitFilePathsResolver;
use Nette\Utils\FileSystem;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

final class ConfigInitializerTest extends TestCase
{
    private const PROMPT = 'Should we generate it for you?';

    private const NON_INTERACTIVE_WARNING = 'No "fractor.php" config found. Create one, or pass "--config <path>".';

    private const DRY_RUN_WARNING = 'No "fractor.php" config found. Create one, or run without --dry-run to generate it.';

    private string $projectDirectory;

    private string|false $previousColumns;

    protected function setUp(): void
    {
        $this->projectDirectory = sys_get_temp_dir() . '/fractor-config-initializer-' . bin2hex(random_bytes(8));
        FileSystem::createDir($this->projectDirectory);

        // the warning block is wrapped at the terminal width, so fix it
        $this->previousColumns = getenv('COLUMNS');
        putenv('COLUMNS=120');
    }

    protected function tearDown(): void
    {
        FileSystem::delete($this->projectDirectory);
        putenv($this->previousColumns === false ? 'COLUMNS' : 'COLUMNS=' . $this->previousColumns);
    }

    public function testGivenConfigWithoutRulesIsNamedAndNoConfigIsCreated(): void
    {
        $output = $this->createConfig($this->writeCustomConfig(), terminal: false);

        self::assertWarning('Register rules or sets in your "build/custom-fractor.php" config', $output);
        self::assertStringNotContainsString('No "fractor.php" config found', $output);
        self::assertFileDoesNotExist($this->projectDirectory . '/fractor.php');
    }

    public function testGivenConfigOnTerminalIsNotPrompted(): void
    {
        $output = $this->createConfig($this->writeCustomConfig(), terminal: true, answer: "yes\n");

        self::assertWarning('Register rules or sets in your "build/custom-fractor.php" config', $output);
        self::assertStringNotContainsString(self::PROMPT, $output);
        self::assertFileDoesNotExist($this->projectDirectory . '/fractor.php');
    }

    public function testGivenConfigIsNamedEvenWithAnotherConfigInProjectDirectory(): void
    {
        FileSystem::write($this->projectDirectory . '/fractor.php', "<?php\n\nreturn 42;\n");

        $output = $this->createConfig($this->writeCustomConfig(), terminal: false);

        self::assertWarning('Register rules or sets in your "build/custom-fractor.php" config', $output);
    }

    public function testGivenConfigOutsideProjectDirectoryIsNamedWithItsFullPath(): void
    {
        $outsideDirectory = $this->projectDirectory . '-outside';
        $mainConfigFile = $outsideDirectory . '/fractor.php';
        FileSystem::write($mainConfigFile, "<?php\n\nreturn 42;\n");

        try {
            $output = $this->createConfig($mainConfigFile, terminal: false);
        } finally {
            FileSystem::delete($outsideDirectory);
        }

        self::assertWarning(sprintf('Register rules or sets in your "%s" config', $mainConfigFile), $output);
    }

    public function testGivenConfigIsNamedWithItsFullPathWithoutWorkingDirectory(): void
    {
        // getcwd() returns false after the working directory was deleted, and ProcessCommand passes an empty string
        $mainConfigFile = $this->writeCustomConfig();

        $output = $this->createConfig($mainConfigFile, terminal: false, projectDirectory: '');

        self::assertWarning(sprintf('Register rules or sets in your "%s" config', $mainConfigFile), $output);
    }

    public function testGivenConfigThatNoLongerExistsFallsBackToTheNonInteractiveWarning(): void
    {
        $output = $this->createConfig($this->projectDirectory . '/missing.php', terminal: false);

        self::assertWarning(self::NON_INTERACTIVE_WARNING, $output);
        self::assertFileDoesNotExist($this->projectDirectory . '/fractor.php');
    }

    public function testExistingConfigInProjectDirectoryIsNamedAndLeftUnchanged(): void
    {
        $projectConfigFile = $this->projectDirectory . '/fractor.php';
        FileSystem::write($projectConfigFile, "<?php\n\nreturn 42;\n");

        $output = $this->createConfig(null, terminal: false);

        self::assertWarning('Register rules or sets in your "fractor.php" config', $output);
        self::assertStringEqualsFile($projectConfigFile, "<?php\n\nreturn 42;\n");
    }

    public function testNonInteractiveInputIsNotPromptedAndNoConfigIsCreated(): void
    {
        $output = $this->createConfig(null, terminal: false);

        self::assertWarning(self::NON_INTERACTIVE_WARNING, $output);
        self::assertStringNotContainsString(self::PROMPT, $output);
        self::assertFileDoesNotExist($this->projectDirectory . '/fractor.php');
    }

    public function testDryRunWithGivenConfigNamesTheConfig(): void
    {
        $output = $this->createConfig($this->writeCustomConfig(), terminal: true, isDryRun: true);

        self::assertWarning('Register rules or sets in your "build/custom-fractor.php" config', $output);
    }

    public function testDryRunWithExistingConfigInProjectDirectoryNamesIt(): void
    {
        FileSystem::write($this->projectDirectory . '/fractor.php', "<?php\n\nreturn 42;\n");

        $output = $this->createConfig(null, terminal: true, isDryRun: true);

        self::assertWarning('Register rules or sets in your "fractor.php" config', $output);
    }

    public function testDryRunWithoutTerminalGetsTheNonInteractiveWarning(): void
    {
        // running without --dry-run would not help without a terminal, so the advice is the one for --config
        $output = $this->createConfig(null, terminal: false, isDryRun: true);

        self::assertWarning(self::NON_INTERACTIVE_WARNING, $output);
        self::assertFileDoesNotExist($this->projectDirectory . '/fractor.php');
    }

    public function testDryRunOnTerminalIsNotPrompted(): void
    {
        $output = $this->createConfig(null, terminal: true, answer: "yes\n", isDryRun: true);

        self::assertWarning(self::DRY_RUN_WARNING, $output);
        self::assertStringNotContainsString(self::PROMPT, $output);
        self::assertFileDoesNotExist($this->projectDirectory . '/fractor.php');
    }

    public function testTerminalIsPromptedAndAnswerNoWritesNothing(): void
    {
        $output = $this->createConfig(null, terminal: true, answer: "no\n");

        self::assertStringContainsString(self::PROMPT, $output);
        self::assertFileDoesNotExist($this->projectDirectory . '/fractor.php');
    }

    public function testTerminalIsPromptedAndAnswerYesWritesConfig(): void
    {
        $output = $this->createConfig(null, terminal: true, answer: "yes\n");

        self::assertStringContainsString(self::PROMPT, $output);
        self::assertFileExists($this->projectDirectory . '/fractor.php');
    }

    private static function assertWarning(string $message, string $output): void
    {
        // the block may wrap inside a long path, so compare the whole message without any whitespace, and every part
        // outside the quotes, which does not wrap at the fixed width, with its spaces
        self::assertStringContainsString(
            (string) preg_replace('/\s+/', '', '[WARNING] ' . $message),
            (string) preg_replace('/\s+/', '', $output)
        );
        foreach (explode('"', '[WARNING] ' . $message) as $index => $part) {
            if ($index % 2 === 0) {
                self::assertStringContainsString($part, $output);
            }
        }
    }

    private function writeCustomConfig(): string
    {
        $mainConfigFile = $this->projectDirectory . '/build/custom-fractor.php';
        FileSystem::write($mainConfigFile, "<?php\n\nreturn 42;\n");

        return $mainConfigFile;
    }

    private function createConfig(
        ?string $mainConfigFile,
        bool $terminal,
        string $answer = '',
        bool $isDryRun = false,
        ?string $projectDirectory = null
    ): string {
        $answerStream = fopen('php://memory', 'r+b');
        self::assertIsResource($answerStream);
        fwrite($answerStream, $answer);
        rewind($answerStream);

        $input = new ArrayInput([]);
        $input->setStream($answerStream);
        $output = new BufferedOutput();

        $configInitializer = new ConfigInitializer(
            [],
            new InitFilePathsResolver(),
            new SymfonyStyle($input, $output),
            new TerminalDetector($terminal ? $this->openTerminal() : $this->openNonTerminal()),
            $mainConfigFile
        );
        $configInitializer->createConfig($projectDirectory ?? $this->projectDirectory, $isDryRun);

        // the warning block wraps long lines, so compare against a single line
        return (string) preg_replace('/\s+/', ' ', $output->fetch());
    }

    /**
     * @return resource
     */
    private function openNonTerminal()
    {
        $stream = fopen('php://memory', 'rb');
        self::assertIsResource($stream);

        return $stream;
    }

    /**
     * @return resource
     */
    private function openTerminal()
    {
        $terminal = @fopen('/dev/ptmx', 'r+b');
        if (! is_resource($terminal) || ! stream_isatty($terminal)) {
            self::markTestSkipped('No pseudo terminal available to stand in for an interactive STDIN');
        }

        return $terminal;
    }
}
