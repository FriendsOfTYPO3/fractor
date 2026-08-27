<?php

declare(strict_types=1);

namespace a9f\FractorXml\Tests\UnparsableXml;

use a9f\Fractor\Testing\PHPUnit\AbstractFractorTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * A file that libxml cannot parse must be reported and left untouched, instead
 * of being truncated to a bare XML declaration.
 */
final class UnparsableXmlTest extends AbstractFractorTestCase
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
        return self::yieldFilesFromDirectory(__DIR__ . '/Fixtures', '*.xml.fixture');
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/fractor.php';
    }
}
