<?php

declare(strict_types=1);

namespace a9f\Fractor\Configuration;

use a9f\Fractor\Caching\ValueObject\Storage\FileCacheStorage;

final class Option
{
    /**
     * @var string
     */
    public const SOURCE = 'source';

    /**
     * @var string
     */
    public const PATHS = 'paths';

    /**
     * @var string
     */
    public const SKIP = 'skip';

    /**
     * @var string
     */
    public const DRY_RUN = 'dry-run';

    /**
     * @var string
     */
    public const DRY_RUN_SHORT = 'n';

    /**
     * @var string
     */
    public const CONFIG = 'config';

    /**
     * @var string
     */
    public const CONFIG_SHORT = 'c';

    public const CACHE_DIR = 'cache_dir';

    /**
     * @var string
     */
    public const ONLY = 'only';

    /**
     * @var string
     */
    public const OUTPUT_FORMAT = 'output-format';

    /**
     * @var string
     */
    public const NO_PROGRESS_BAR = 'no-progress-bar';

    /**
     * @var string
     */
    public const SHOW_CHANGELOG = 'show-changelog';

    public const CACHE_CLASS = FileCacheStorage::class;

    public const CONTAINER_CACHE_DIRECTORY = 'container-cache-directory';

    public const CLEAR_CACHE = 'clear-cache';

    public const REGISTERED_FRACTOR_RULES = 'registered_fractor_rules';

    /**
     * @internal the config file given with --config, or the fractor.php found in the working directory; null if neither exists
     */
    public const MAIN_CONFIG_FILE = 'main_config_file';

    public const OPTIONS = 'options';

    /**
     * @internal to allow process file without extension if explicitly registered
     */
    public const FILES_WITHOUT_EXTENSION = 'files_without_extension';

    /**
     * @var string
     */
    public const NO_DIFFS = 'no-diffs';

    /**
     * @var string
     */
    public const MEMORY_LIMIT = 'memory-limit';

    /**
     * @var string
     */
    public const RULES_SUMMARY = 'rules-summary';
}
