<?php

declare(strict_types=1);

namespace a9f\Fractor\Tests\StaticReflection;

use a9f\Fractor\FileSystem\FileAndDirectoryFilter;
use a9f\Fractor\FileSystem\FilesystemTweaker;
use a9f\Fractor\StaticReflection\DynamicSourceLocatorDecorator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class DynamicSourceLocatorDecoratorTest extends TestCase
{
    private DynamicSourceLocatorDecorator $dynamicSourceLocatorDecorator;

    protected function setUp(): void
    {
        $this->dynamicSourceLocatorDecorator = new DynamicSourceLocatorDecorator(
            new FileAndDirectoryFilter(),
            new FilesystemTweaker()
        );
    }

    #[Test]
    public function existingFilesAndDirectoriesAreAdded(): void
    {
        $addedPaths = $this->dynamicSourceLocatorDecorator->addPaths([__DIR__, __FILE__]);

        self::assertSame([__FILE__, __DIR__], $addedPaths);
        self::assertFalse($this->dynamicSourceLocatorDecorator->arePathsEmpty());
    }

    #[Test]
    public function globPatternsAreResolved(): void
    {
        $addedPaths = $this->dynamicSourceLocatorDecorator->addPaths([__DIR__ . '/*Test.php']);

        self::assertSame([__FILE__], $addedPaths);
    }

    #[Test]
    public function nonExistingPathsLeavePathsEmpty(): void
    {
        $addedPaths = $this->dynamicSourceLocatorDecorator->addPaths([__DIR__ . '/does-not-exist']);

        self::assertSame([], $addedPaths);
        self::assertTrue($this->dynamicSourceLocatorDecorator->arePathsEmpty());
    }

    #[Test]
    public function noPathsLeavePathsEmpty(): void
    {
        self::assertSame([], $this->dynamicSourceLocatorDecorator->addPaths([]));
        self::assertTrue($this->dynamicSourceLocatorDecorator->arePathsEmpty());
    }
}
