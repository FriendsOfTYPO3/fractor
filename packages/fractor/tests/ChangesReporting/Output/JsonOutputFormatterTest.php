<?php

declare(strict_types=1);

namespace a9f\Fractor\Tests\ChangesReporting\Output;

use a9f\Fractor\ChangesReporting\Output\JsonOutputFormatter;
use a9f\Fractor\Configuration\ValueObject\Configuration;
use a9f\Fractor\Differ\ValueObject\FileDiff;
use a9f\Fractor\ValueObject\ProcessResult;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class JsonOutputFormatterTest extends TestCase
{
    /**
     * A processor that normalises formatting rewrites the diff baseline, so a
     * formatting-only change carries an empty diff and is filtered out of
     * getFileDiffs() — but the file WILL be rewritten by a non-dry run. The
     * totals must count those files, not the surviving diffs.
     */
    #[Test]
    public function totalsCountChangedFilesEvenWhenTheirDiffsAreEmpty(): void
    {
        $processResult = new ProcessResult([], 46);

        $json = $this->render($processResult);

        self::assertSame(46, $json['totals']['changed_files']);
    }

    #[Test]
    public function formattingOnlyChangesAreListedWithoutADiffBody(): void
    {
        $emptyDiff = new FileDiff('packages/some/locallang.xlf', '', '');
        $processResult = new ProcessResult([$emptyDiff], 1);

        $json = $this->render($processResult);

        self::assertSame(1, $json['totals']['changed_files']);
        self::assertSame(['packages/some/locallang.xlf'], $json['changed_files']);
        self::assertArrayNotHasKey('file_diffs', $json);
    }

    #[Test]
    public function fileDiffsAreListedWhenPresent(): void
    {
        $fileDiff = new FileDiff('packages/some/File.php', '--- a\n+++ b', '');
        $processResult = new ProcessResult([$fileDiff], 1);

        $json = $this->render($processResult);

        self::assertSame(1, $json['totals']['changed_files']);
        self::assertSame(['packages/some/File.php'], $json['changed_files']);
        self::assertCount(1, $json['file_diffs']);
    }

    /**
     * @return array<string, mixed>
     */
    private function render(ProcessResult $processResult): array
    {
        $configuration = new Configuration(paths: ['/tmp']);

        ob_start();
        (new JsonOutputFormatter())->report($processResult, $configuration);
        $output = (string) ob_get_clean();

        return json_decode($output, true, 512, JSON_THROW_ON_ERROR);
    }
}
