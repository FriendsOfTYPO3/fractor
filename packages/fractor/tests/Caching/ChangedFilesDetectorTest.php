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
    private Cache $cache;

    private ChangedFilesDetector $changedFilesDetector;

    private string $filePath;

    protected function setUp(): void
    {
        $this->cache = new Cache(new MemoryCacheStorage());
        $this->changedFilesDetector = $this->createDetector();
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

    #[Test]
    public function anotherConfigDoesNotReuseCachedFile(): void
    {
        $this->changedFilesDetector->setFirstResolvedConfigFileInfo('/project/Tests/fractor.14.php');
        $this->cacheFileInScope(null);

        $detectorWithOtherConfig = $this->createDetector();
        $detectorWithOtherConfig->setFirstResolvedConfigFileInfo('/project/vendor/as/standards/fractor.14.php');

        self::assertTrue($detectorWithOtherConfig->hasFileChanged($this->filePath));
    }

    #[Test]
    public function sameConfigReusesCachedFile(): void
    {
        $this->changedFilesDetector->setFirstResolvedConfigFileInfo('/project/fractor.php');
        $this->cacheFileInScope(null);

        $detectorWithSameConfig = $this->createDetector();
        $detectorWithSameConfig->setFirstResolvedConfigFileInfo('/project/fractor.php');

        self::assertFalse($detectorWithSameConfig->hasFileChanged($this->filePath));
    }

    private function createDetector(): ChangedFilesDetector
    {
        return new ChangedFilesDetector(new FileHashComputer(), $this->cache, new FileHasher());
    }

    private function cacheFileInScope(?string $onlyRule): void
    {
        $this->changedFilesDetector->setActiveScope($onlyRule);
        $this->changedFilesDetector->addCachableFile($this->filePath);
        $this->changedFilesDetector->cacheFile($this->filePath);
    }
}
