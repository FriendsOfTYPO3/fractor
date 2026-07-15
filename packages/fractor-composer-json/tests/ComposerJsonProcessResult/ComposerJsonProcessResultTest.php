<?php

declare(strict_types=1);

namespace a9f\FractorComposerJson\Tests\ComposerJsonProcessResult;

use a9f\Fractor\Application\ValueObject\AppliedRule;
use a9f\Fractor\Testing\PHPUnit\AbstractFractorTestCase;
use a9f\FractorComposerJson\RemovePackageComposerJsonFractor;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * The composer.json processor re-prints files it touches, so a formatting-only
 * change (indentation) must be attributed to the virtual CodeFormatRule — so a
 * re-indentation is reported rather than applied silently — while a real
 * transformation stays attributed to its own rule.
 */
final class ComposerJsonProcessResultTest extends AbstractFractorTestCase
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
            'rule-change/composer.json.fixture',
            [RemovePackageComposerJsonFractor::class],
        ];
        yield 'pure reformat is attributed to the virtual code-format rule' => [
            'reformatting/composer.json.fixture',
            [AppliedRule::CODE_FORMAT_RULE],
        ];
        yield 'already formatted file reports no change' => ['already-formatted/composer.json.fixture', []];
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/fractor.php';
    }
}
