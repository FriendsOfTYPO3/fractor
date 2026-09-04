<?php

declare(strict_types=1);

namespace a9f\FractorDocGenerator\Tests\Differ\FullContextDiffOutputBuilder;

use a9f\FractorDocGenerator\Differ\FullContextDiffOutputBuilder;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\Diff\Differ;

final class FullContextDiffOutputBuilderTest extends TestCase
{
    private Differ $differ;

    protected function setUp(): void
    {
        $this->differ = new Differ(new FullContextDiffOutputBuilder());
    }

    #[DataProvider('provideData')]
    public function test(string $old, string $new, string $expected): void
    {
        self::assertSame($expected, $this->differ->diff($old, $new));
    }

    public static function provideData(): \Iterator
    {
        yield 'unchanged lines are kept as context' => [
            "line one\nline two\nline three\n",
            "line one\nline TWO\nline three\n",
            " line one\n-line two\n+line TWO\n line three\n",
        ];

        yield 'input without a trailing newline gets no eof marker text' => [
            'old code',
            'new code',
            "-old code\n+new code\n",
        ];

        yield 'addition only' => ["a\n", "a\nb\n", " a\n+b\n"];

        yield 'removal only' => ["a\nb\n", "a\n", " a\n-b\n"];

        yield 'identical input produces an empty diff' => ["same\n", "same\n", ''];

        yield 'line ending warning is rendered as context' => [
            "a\r\nb\n",
            "a\nB\n",
            " #Warning: Strings contain different line endings!\n-a\r\n-b\n+a\n+B\n",
        ];

        yield 'trailing context line without a newline' => ["a\nb", "A\nb", "-a\n+A\n b\n"];

        yield 'two distant changes with context between them' => [
            "l1\nl2\na\nl4\nl5\nl6\nl7\nl8\nl9\nl10\nb\nl12\n",
            "l1\nl2\nA\nl4\nl5\nl6\nl7\nl8\nl9\nl10\nB\nl12\n",
            " l1\n l2\n-a\n+A\n l4\n l5\n l6\n l7\n l8\n l9\n l10\n-b\n+B\n l12\n",
        ];
    }

    public function testEmitsNoUnifiedDiffMetadata(): void
    {
        $diff = $this->differ->diff("a\nb\nc\n", "a\nB\nc\n");

        self::assertStringNotContainsString('@@', $diff);
        self::assertStringNotContainsString('---', $diff);
        self::assertStringNotContainsString('+++', $diff);
        self::assertStringNotContainsString('No newline at end of file', $diff);
    }
}
