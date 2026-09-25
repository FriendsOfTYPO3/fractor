<?php

declare(strict_types=1);

namespace a9f\FractorXml\Tests\XmlProcessResult;

use a9f\Fractor\Application\ValueObject\AppliedRule;
use a9f\Fractor\Testing\PHPUnit\AbstractFractorTestCase;
use a9f\FractorXml\Tests\Fixtures\DummyXmlFractorRule;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * The XML processor re-indents files it touches, so a formatting-only reformat
 * must be attributed to the virtual CodeFormatRule (and count toward the exit
 * code), while a real transformation stays attributed to its own rule.
 */
final class XmlProcessResultTest extends AbstractFractorTestCase
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
        yield 'rule change is attributed to the real rule' => ['rule-change.xml.fixture', [DummyXmlFractorRule::class]];
        yield 'pure reformat is attributed to the virtual code-format rule' => [
            'reformatting.xml.fixture',
            [AppliedRule::CODE_FORMAT_RULE],
        ];
        yield 'rule change and reformat are both reported' => [
            'rule-and-formatting.xml.fixture',
            [AppliedRule::CODE_FORMAT_RULE, DummyXmlFractorRule::class],
        ];
        yield 'already formatted file reports no change' => ['already-formatted.xml.fixture', []];
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/fractor.php';
    }
}
