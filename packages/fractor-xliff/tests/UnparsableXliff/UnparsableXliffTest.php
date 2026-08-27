<?php

declare(strict_types=1);

namespace a9f\FractorXliff\Tests\UnparsableXliff;

use a9f\Fractor\Testing\PHPUnit\AbstractFractorTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * A file that libxml cannot parse must be reported and left untouched, instead
 * of aborting the whole run.
 */
final class UnparsableXliffTest extends AbstractFractorTestCase
{
    #[DataProvider('provideData')]
    public function test(string $filePath): void
    {
        // no split marker in the fixtures: the content must stay as it is
        $this->doTestFile($filePath);
        $this->assertThatFileCouldNotBeProcessed('The file is not well-formed XML');
    }

    public static function provideData(): \Iterator
    {
        return self::yieldFilesFromDirectory(__DIR__ . '/Fixtures', '*.xlf.fixture');
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/fractor.php';
    }
}
