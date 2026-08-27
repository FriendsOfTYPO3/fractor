<?php

declare(strict_types=1);

namespace a9f\Fractor\ValueObject\Error;

use a9f\Fractor\Application\Contract\FileProcessor;
use a9f\Fractor\Application\Contract\FractorRule;
use Nette\Utils\Strings;

/**
 * A file that could not be processed. The file is left untouched on disk and the
 * run continues with the remaining files.
 */
final readonly class SystemError
{
    /**
     * @param class-string<FileProcessor<FractorRule>> $processorClass
     */
    public function __construct(
        private string $message,
        private string $relativeFilePath,
        private string $processorClass
    ) {
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getRelativeFilePath(): string
    {
        return $this->relativeFilePath;
    }

    public function getAbsoluteFilePath(): ?string
    {
        return \realpath($this->relativeFilePath) ?: null;
    }

    /**
     * @return class-string<FileProcessor<FractorRule>>
     */
    public function getProcessorClass(): string
    {
        return $this->processorClass;
    }

    public function getProcessorShortClass(): string
    {
        return (string) Strings::after($this->processorClass, '\\', -1);
    }
}
