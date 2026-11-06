<?php

declare(strict_types=1);

namespace a9f\Typo3Fractor\Tests\TYPO3v13\TypoScript\MigratePluginContentElementAndPluginSubtypesFractor;

use a9f\Fractor\Testing\PHPUnit\AbstractFractorTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class MigratePluginContentElementAndPluginSubtypesFractorTest extends AbstractFractorTestCase
{
    #[DataProvider('provideData')]
    public function test(string $filePath): void
    {
        $this->doTestFile($filePath);
    }

    public static function provideData(): \Iterator
    {
        return self::yieldFilesFromDirectory(__DIR__ . '/Fixtures', '*.typoscript.fixture');
    }

    public function provideConfigFilePath(): ?string
    {
        return __DIR__ . '/config/fractor.php';
    }
}
