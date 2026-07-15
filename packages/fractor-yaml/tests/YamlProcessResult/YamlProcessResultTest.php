<?php

declare(strict_types=1);

namespace a9f\FractorYaml\Tests\YamlProcessResult;

use a9f\Fractor\Application\ValueObject\AppliedRule;
use a9f\Fractor\Testing\PHPUnit\AbstractFractorTestCase;
use a9f\FractorYaml\Tests\Fixtures\DummyYamlFractorRule;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * The YAML dumper cannot preserve comments, so the processor only re-dumps files
 * a rule already rewrites — never for formatting alone. On such a file the rule
 * stays attributed to itself, and any reformatting that rides along (indentation,
 * lost comments) is additionally attributed to the virtual CodeFormatRule. A file
 * no rule touches is left byte-exact, even when it carries comments.
 */
final class YamlProcessResultTest extends AbstractFractorTestCase
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
        yield 'rule change on canonical input is attributed to the real rule' => [
            'rule-only.yaml.fixture',
            [DummyYamlFractorRule::class],
        ];
        yield 'reformatting that rides along a rule is also attributed to the code-format rule' => [
            'rule-and-reformat.yaml.fixture',
            [AppliedRule::CODE_FORMAT_RULE, DummyYamlFractorRule::class],
        ];
        yield 'a file no rule touches is left byte-exact' => ['untouched-with-comment.yaml.fixture', []];
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/fractor.php';
    }
}
