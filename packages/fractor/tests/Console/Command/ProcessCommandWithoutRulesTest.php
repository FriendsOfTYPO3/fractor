<?php

declare(strict_types=1);

namespace a9f\Fractor\Tests\Console\Command;

use a9f\Fractor\Application\FractorRunner;
use a9f\Fractor\Configuration\ConfigInitializer;
use a9f\Fractor\Configuration\ConfigurationFactory;
use a9f\Fractor\Configuration\ConfigurationRuleFilter;
use a9f\Fractor\Console\Application\FractorApplication;
use a9f\Fractor\Console\Command\ProcessCommand;
use a9f\Fractor\Console\Output\OutputFormatterCollector;
use a9f\Fractor\Console\TerminalDetector;
use a9f\Fractor\FileSystem\InitFilePathsResolver;
use a9f\Fractor\Testing\PHPUnit\AbstractFractorTestCase;
use a9f\Fractor\Util\MemoryLimiter;
use Nette\Utils\FileSystem;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * ProcessCommand hands --dry-run to the ConfigInitializer when no rules are loaded. The container's ConfigInitializer
 * writes to a silenced output in tests, so the command gets one that writes to a buffer and reads from a terminal.
 */
final class ProcessCommandWithoutRulesTest extends AbstractFractorTestCase
{
    private string $projectDirectory;

    private string $previousWorkingDirectory;

    private BufferedOutput $configInitializerOutput;

    protected function setUp(): void
    {
        parent::setUp();

        $this->projectDirectory = sys_get_temp_dir() . '/fractor-process-without-rules-' . bin2hex(random_bytes(8));
        FileSystem::createDir($this->projectDirectory);
        $this->previousWorkingDirectory = (string) getcwd();
        chdir($this->projectDirectory);
    }

    protected function tearDown(): void
    {
        chdir($this->previousWorkingDirectory);
        FileSystem::delete($this->projectDirectory);

        parent::tearDown();
    }

    public function testDryRunIsPassedOn(): void
    {
        $this->createCommandTester()
            ->execute([
                '--dry-run' => true,
            ]);

        $output = $this->getConfigInitializerOutput();
        self::assertStringContainsString('run without --dry-run to generate it', $output);
        self::assertStringNotContainsString('Should we generate it for you?', $output);
    }

    public function testWithoutDryRunThePromptAppears(): void
    {
        $this->createCommandTester()
            ->execute([]);

        self::assertStringContainsString('Should we generate it for you?', $this->getConfigInitializerOutput());
        self::assertFileDoesNotExist($this->projectDirectory . '/fractor.php');
    }

    private function createCommandTester(): CommandTester
    {
        $terminal = @fopen('/dev/ptmx', 'r+b');
        if (! is_resource($terminal) || ! stream_isatty($terminal)) {
            self::markTestSkipped('No pseudo terminal available to stand in for an interactive STDIN');
        }

        $answerStream = fopen('php://memory', 'r+b');
        self::assertIsResource($answerStream);
        fwrite($answerStream, "no\n");
        rewind($answerStream);
        $input = new ArrayInput([]);
        $input->setStream($answerStream);
        $this->configInitializerOutput = new BufferedOutput();

        $processCommand = new ProcessCommand(
            $this->getService(FractorRunner::class),
            $this->getService(ConfigurationFactory::class),
            $this->getService(OutputFormatterCollector::class),
            new ConfigInitializer(
                [],
                new InitFilePathsResolver(),
                new SymfonyStyle($input, $this->configInitializerOutput),
                new TerminalDetector($terminal),
                null
            ),
            $this->getService(MemoryLimiter::class),
            $this->getService(ConfigurationRuleFilter::class)
        );
        $processCommand->setApplication($this->getService(FractorApplication::class));

        return new CommandTester($processCommand);
    }

    private function getConfigInitializerOutput(): string
    {
        return (string) preg_replace('/\s+/', ' ', $this->configInitializerOutput->fetch());
    }
}
