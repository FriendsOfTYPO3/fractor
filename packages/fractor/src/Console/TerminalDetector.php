<?php

declare(strict_types=1);

namespace a9f\Fractor\Console;

/**
 * Detects whether Fractor reads from an interactive terminal, so that non-interactive callers - pipes, CI,
 * agents - are not prompted.
 */
final readonly class TerminalDetector
{
    /**
     * @param resource|null $inputStream the stream to check, STDIN if null
     */
    public function __construct(
        private mixed $inputStream = null
    ) {
    }

    public function isInputTty(): bool
    {
        $inputStream = $this->inputStream ?? (defined('STDIN') ? STDIN : null);

        return is_resource($inputStream) && stream_isatty($inputStream);
    }
}
