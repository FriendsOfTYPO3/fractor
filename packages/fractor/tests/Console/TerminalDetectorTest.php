<?php

declare(strict_types=1);

namespace a9f\Fractor\Tests\Console;

use a9f\Fractor\Console\TerminalDetector;
use PHPUnit\Framework\TestCase;

final class TerminalDetectorTest extends TestCase
{
    public function testInjectedStreamIsChecked(): void
    {
        $terminal = @fopen('/dev/ptmx', 'r+b');
        if (! is_resource($terminal) || ! stream_isatty($terminal)) {
            self::markTestSkipped('No pseudo terminal available');
        }

        $nonTerminal = fopen('php://memory', 'rb');
        self::assertIsResource($nonTerminal);

        self::assertTrue((new TerminalDetector($terminal))->isInputTty());
        self::assertFalse((new TerminalDetector($nonTerminal))->isInputTty());
    }

    public function testClosedStreamIsNoTerminal(): void
    {
        $stream = fopen('php://memory', 'rb');
        self::assertIsResource($stream);
        fclose($stream);

        self::assertFalse((new TerminalDetector($stream))->isInputTty());
    }

    public function testWithoutInjectedStreamStdinIsChecked(): void
    {
        // the container creates the detector without a stream, so STDIN is what production checks: run a PHP process
        // once with a pseudo terminal and once with a pipe as its STDIN
        self::assertSame('1', $this->detectInChildProcess(['pty']));
        self::assertSame('0', $this->detectInChildProcess(['pipe', 'r']));
    }

    /**
     * @param list<string> $stdinDescriptor
     */
    private function detectInChildProcess(array $stdinDescriptor): string
    {
        $autoloadFile = dirname(__DIR__, 4) . '/vendor/autoload.php';
        if (! is_file($autoloadFile)) {
            self::markTestSkipped('Autoloader of the monorepo not found');
        }

        $code = sprintf(
            'require %s; echo (int) (new %s())->isInputTty();',
            var_export($autoloadFile, true),
            TerminalDetector::class
        );
        $process = @proc_open(
            [PHP_BINARY, '-r', $code],
            [
                0 => $stdinDescriptor,
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes
        );
        if (! is_resource($process)) {
            self::markTestSkipped('proc_open cannot create this STDIN');
        }

        $output = (string) stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);

        return $output;
    }
}
