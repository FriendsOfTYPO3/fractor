<?php

declare(strict_types=1);

namespace a9f\Fractor\Tests\FileSystem\Skipper;

use a9f\Fractor\FileSystem\Skipper\FilePathNormalizer;
use a9f\Fractor\FileSystem\Skipper\FnMatchPathNormalizer;
use a9f\Fractor\Testing\PHPUnit\AbstractFractorTestCase;
use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;

final class FnMatchPathNormalizerTest extends AbstractFractorTestCase
{
    #[DataProvider('providePaths')]
    public function testPaths(string $path, string $expectedNormalizedPath): void
    {
        $normalizedPath = FnMatchPathNormalizer::normalizeForFnmatch($path);
        self::assertSame($expectedNormalizedPath, $normalizedPath);
    }

    public static function providePaths(): Iterator
    {
        yield ['path/with/no/asterisk', 'path/with/no/asterisk'];
        yield ['*path/with/asterisk/begin', '*path/with/asterisk/begin*'];
        yield ['path/with/asterisk/end*', '*path/with/asterisk/end*'];
        yield ['*path/with/asterisk/begin/and/end*', '*path/with/asterisk/begin/and/end*'];
        yield [
            __DIR__ . '/Fixtures/path/with/../in/it',
            FilePathNormalizer::normalizeDirectorySeparator(__DIR__ . '/Fixtures/path/in/it'),
        ];
        yield [
            __DIR__ . '/Fixtures/path/with/../../in/it',
            FilePathNormalizer::normalizeDirectorySeparator(__DIR__ . '/Fixtures/in/it'),
        ];
    }
}
