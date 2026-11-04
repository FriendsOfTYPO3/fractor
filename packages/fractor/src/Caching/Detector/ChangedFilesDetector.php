<?php

declare(strict_types=1);

namespace a9f\Fractor\Caching\Detector;

use a9f\Fractor\Caching\Cache;
use a9f\Fractor\Caching\Config\FileHashComputer;
use a9f\Fractor\Caching\Enum\CacheKey;
use a9f\Fractor\Helper\FileHasher;

/**
 * Inspired by https://github.com/symplify/symplify/pull/90/files#diff-72041b2e1029a08930e13d79d298ef11
 */
final class ChangedFilesDetector
{
    /**
     * @var array<string, true>
     */
    private array $cachableFiles = [];

    private string $scopeSuffix = '';

    private string $configSuffix = '';

    public function __construct(
        private readonly FileHashComputer $fileHashComputer,
        private readonly Cache $cache,
        private readonly FileHasher $fileHasher
    ) {
    }

    /**
     * Gives an --only run its own cache entries, so it cannot mark files as unchanged for a full run.
     */
    public function setActiveScope(?string $onlyRule): void
    {
        $this->scopeSuffix = $onlyRule === null ? '' : '|only:' . $onlyRule;
    }

    public function cacheFile(string $filePath): void
    {
        $filePathCacheKey = $this->getFilePathCacheKey($filePath);
        if (! isset($this->cachableFiles[$filePathCacheKey])) {
            return;
        }
        $hash = $this->hashFile($filePath);
        $this->cache->save($filePathCacheKey, CacheKey::FILE_HASH_KEY, $hash);
    }

    public function addCachableFile(string $filePath): void
    {
        $filePathCacheKey = $this->getFilePathCacheKey($filePath);
        $this->cachableFiles[$filePathCacheKey] = \true;
    }

    public function hasFileChanged(string $filePath): bool
    {
        $fileInfoCacheKey = $this->getFilePathCacheKey($filePath);
        $cachedValue = $this->cache->load($fileInfoCacheKey, CacheKey::FILE_HASH_KEY);
        if ($cachedValue !== null) {
            $currentFileHash = $this->hashFile($filePath);
            return $currentFileHash !== $cachedValue;
        }
        // we don't have a value to compare against. Be defensive and assume its changed
        return \true;
    }

    public function invalidateFile(string $filePath): void
    {
        $fileInfoCacheKey = $this->getFilePathCacheKey($filePath);
        $this->cache->clean($fileInfoCacheKey);
        unset($this->cachableFiles[$fileInfoCacheKey]);
    }

    public function clear(): void
    {
        $this->cache->clear();
    }

    /**
     * Ties every cache entry to the config fingerprint, so a run with another config
     * or other rule options never reuses an "unchanged" verdict of this one.
     *
     * @api
     */
    public function setFirstResolvedConfigFileInfo(string $filePath): void
    {
        $configHash = $this->fileHashComputer->compute($filePath);
        $this->storeConfigurationDataHash($filePath, $configHash);
        $this->configSuffix = '|config:' . $configHash;
    }

    private function resolvePath(string $filePath): string
    {
        $realPath = \realpath($filePath);
        if ($realPath === \false) {
            return $filePath;
        }
        return $realPath;
    }

    private function getFilePathCacheKey(string $filePath): string
    {
        return $this->fileHasher->hash($this->resolvePath($filePath) . $this->configSuffix . $this->scopeSuffix);
    }

    private function hashFile(string $filePath): string
    {
        return $this->fileHasher->hashFiles([$this->resolvePath($filePath)]);
    }

    private function storeConfigurationDataHash(string $filePath, string $configurationHash): void
    {
        $key = CacheKey::CONFIGURATION_HASH_KEY . '_' . $this->fileHasher->hash($this->resolvePath($filePath));
        $this->invalidateCacheIfConfigurationChanged($key, $configurationHash);
        $this->cache->save($key, CacheKey::CONFIGURATION_HASH_KEY, $configurationHash);
    }

    private function invalidateCacheIfConfigurationChanged(string $key, string $configurationHash): void
    {
        $oldCachedValue = $this->cache->load($key, CacheKey::CONFIGURATION_HASH_KEY);
        if ($oldCachedValue === null) {
            return;
        }
        if ($oldCachedValue === $configurationHash) {
            return;
        }
        // should be unique per getcwd()
        $this->clear();
    }
}
