<?php

declare(strict_types=1);

namespace a9f\FractorDocGenerator\ValueObject;

final readonly class RuleClassWithFilePath
{
    /**
     * @param class-string $class
     */
    public function __construct(
        private string $class,
        private string $path
    ) {
    }

    /**
     * @return class-string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    public function getPath(): string
    {
        return $this->path;
    }
}
