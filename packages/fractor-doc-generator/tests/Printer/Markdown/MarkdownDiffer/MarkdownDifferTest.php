<?php

declare(strict_types=1);

namespace a9f\FractorDocGenerator\Tests\Printer\Markdown\MarkdownDiffer;

use a9f\FractorDocGenerator\Printer\Markdown\MarkdownDiffer;
use a9f\FractorDocGenerator\Tests\AbstractTestCase;

final class MarkdownDifferTest extends AbstractTestCase
{
    public function test(): void
    {
        $markdownDiffer = $this->getService(MarkdownDiffer::class);

        $currentDiff = $markdownDiffer->diff('old code', 'new code');
        self::assertSame('```diff
-old code
+new code
```
', $currentDiff);
    }

    public function testKeepsUnchangedLinesAsContext(): void
    {
        $markdownDiffer = $this->getService(MarkdownDiffer::class);

        $currentDiff = $markdownDiffer->diff("keep\nold\nkeep2\n", "keep\nnew\nkeep2\n");

        self::assertSame('```diff
 keep
-old
+new
 keep2
```
', $currentDiff);
    }
}
