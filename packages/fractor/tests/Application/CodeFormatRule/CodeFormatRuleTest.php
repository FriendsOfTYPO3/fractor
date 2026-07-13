<?php

declare(strict_types=1);

namespace a9f\Fractor\Tests\Application\CodeFormatRule;

use a9f\Fractor\Application\ValueObject\AppliedRule;
use a9f\Fractor\Testing\PHPUnit\AbstractFractorTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * The virtual CodeFormatRule lives in the runner, not in any single processor:
 * whenever a processor re-formats a file without attributing a real rule, the
 * change must be recognised as code formatting. This exercises that path through
 * a plain text processor to prove it is processor-agnostic.
 */
final class CodeFormatRuleTest extends AbstractFractorTestCase
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
        // Trailing whitespace gets trimmed by a rule that records nothing: the
        // runner must recognise the change as code formatting on its own.
        yield 'reformat without a real rule is attributed to the code-format rule' => [
            'trailing-whitespace.txt.fixture',
            [AppliedRule::CODE_FORMAT_RULE],
        ];
        // Nothing to trim: no change is reported and no rule is applied.
        yield 'already clean file reports no change' => ['clean.txt.fixture', []];
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/fractor.php';
    }

    protected function additionalConfigurationFiles(): array
    {
        return [__DIR__ . '/../FractorRunner/config/config.php'];
    }
}
