<?php

declare(strict_types=1);

namespace a9f\Fractor\Exception;

/**
 * Thrown by a file processor when a file cannot be parsed.
 *
 * The file is reported and left untouched on disk, and the run continues with
 * the remaining files.
 */
final class UnparsableFileException extends \RuntimeException
{
}
