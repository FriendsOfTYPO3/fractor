<?php

declare(strict_types=1);

namespace a9f\FractorXliff\Tests\XliffProcessResult;

use a9f\Fractor\Application\ValueObject\AppliedRule;
use a9f\Fractor\Testing\PHPUnit\AbstractFractorTestCase;
use a9f\FractorXliff\Tests\Fixtures\DummyXliffFractorRule;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * A change (rule or re-formatting) must be reported and count toward the dry-run
 * exit code; a pure reformat is attributed to the virtual CodeFormatRule, a real
 * rule to itself, and an unchanged file reports nothing.
 */
final class XliffProcessResultTest extends AbstractFractorTestCase
{
    /**
     * @param list<string> $expectedAppliedRules
     */
    #[DataProvider('provideData')]
    public function test(string $fixture, array $expectedAppliedRules): void
    {
        $fractorTestResult = $this->doTestFile(__DIR__ . '/Fixtures/' . $fixture);

        self::assertSame($expectedAppliedRules, $fractorTestResult->getAppliedFractorRules());
    }

    /**
     * @return \Iterator<string, array{string, list<string>}>
     */
    public static function provideData(): \Iterator
    {
        yield 'rule change is attributed to the real rule' => [
            'rule-change.xlf.fixture',
            [DummyXliffFractorRule::class],
        ];
        yield 'pure reformat is attributed to the virtual code-format rule' => [
            'reformatting.xlf.fixture',
            [AppliedRule::CODE_FORMAT_RULE],
        ];
        yield 'rule change and reformat are both reported' => [
            'rule-and-formatting.xlf.fixture',
            [AppliedRule::CODE_FORMAT_RULE, DummyXliffFractorRule::class],
        ];
        yield 'already formatted file reports no change' => ['already-formatted.xlf.fixture', []];
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/fractor.php';
    }
}
