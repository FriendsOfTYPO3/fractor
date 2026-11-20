<?php

declare(strict_types=1);

namespace a9f\FractorXml\Printer;

/**
 * The file could not be matched to the DOM, so the printer falls back to the fixed form.
 */
final class SourceMismatchException extends \RuntimeException
{
}
