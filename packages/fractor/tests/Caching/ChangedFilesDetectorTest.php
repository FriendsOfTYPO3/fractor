<?php

declare(strict_types=1);

namespace a9f\Fractor\Tests\Caching;

use a9f\Fractor\Caching\Cache;
use a9f\Fractor\Caching\Config\FileHashComputer;
use a9f\Fractor\Caching\Detector\ChangedFilesDetector;
use a9f\Fractor\Caching\ValueObject\Storage\MemoryCacheStorage;
use a9f\Fractor\Helper\FileHasher;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ChangedFilesDetectorTest extends TestCase
{
    private ChangedFilesDetector $changedFilesDetector;

    private string $filePath;

    protected function setUp(): void
    {
        $this->changedFilesDetector = new ChangedFilesDetector(
            new FileHashComputer(),
            new Cache(new MemoryCacheStorage()),
            new FileHasher()
        );
        $this->filePath = (string) tempnam(sys_get_temp_dir(), 'fractor_detector_');
        file_put_contents($this->filePath, 'content');
    }

    protected function tearDown(): void
    {
        @unlink($this->filePath);
    }

    #[Test]
    public function onlyRunDoesNotMarkFilesUnchangedForFullRun(): void
    {
        $this->cacheFileInScope('Vendor\\SomeFractor');

        $this->changedFilesDetector->setActiveScope(null);
        self::assertTrue($this->changedFilesDetector->hasFileChanged($this->filePath));
    }

    #[Test]
    public function onlyRunDoesNotMarkFilesUnchangedForAnotherOnlyRun(): void
    {
        $this->cacheFileInScope('Vendor\\SomeFractor');

        $this->changedFilesDetector->setActiveScope('Vendor\\OtherFractor');
        self::assertTrue($this->changedFilesDetector->hasFileChanged($this->filePath));
    }

    #[Test]
    public function sameScopeReusesCachedFile(): void
    {
        $this->cacheFileInScope('Vendor\\SomeFractor');

        self::assertFalse($this->changedFilesDetector->hasFileChanged($this->filePath));
    }

    private function cacheFileInScope(?string $onlyRule): void
    {
        $this->changedFilesDetector->setActiveScope($onlyRule);
        $this->changedFilesDetector->addCachableFile($this->filePath);
        $this->changedFilesDetector->cacheFile($this->filePath);
    }
}
