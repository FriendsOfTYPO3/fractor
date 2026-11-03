<?php

declare(strict_types=1);

namespace a9f\Fractor\StaticReflection;

use a9f\Fractor\FileSystem\FileAndDirectoryFilter;
use a9f\Fractor\FileSystem\FilesystemTweaker;

/**
 * Mirrors \Rector\StaticReflection\DynamicSourceLocatorDecorator; without static
 * reflection in Fractor, it keeps the resolved paths itself.
 */
final class DynamicSourceLocatorDecorator
{
    /**
     * @var string[]
     */
    private array $files = [];

    /**
     * @var string[]
     */
    private array $directories = [];

    public function __construct(
        private readonly FileAndDirectoryFilter $fileAndDirectoryFilter,
        private readonly FilesystemTweaker $filesystemTweaker
    ) {
    }

    /**
     * @param string[] $paths
     * @return string[]
     */
    public function addPaths(array $paths): array
    {
        if ($paths === []) {
            return [];
        }

        $paths = $this->filesystemTweaker->resolveWithFnmatch($paths);

        $files = $this->fileAndDirectoryFilter->filterFiles($paths);
        $this->files = array_merge($this->files, $files);

        $directories = $this->fileAndDirectoryFilter->filterDirectories($paths);
        $this->directories = array_merge($this->directories, $directories);

        return array_merge($files, $directories);
    }

    public function arePathsEmpty(): bool
    {
        return $this->files === [] && $this->directories === [];
    }
}
