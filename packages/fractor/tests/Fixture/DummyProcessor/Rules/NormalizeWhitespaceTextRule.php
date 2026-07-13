<?php

declare(strict_types=1);

namespace a9f\Fractor\Tests\Fixture\DummyProcessor\Rules;

use a9f\Fractor\Application\ValueObject\File;
use a9f\Fractor\Tests\Fixture\DummyProcessor\Contract\TextRule;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Trims trailing whitespace from every line: a purely cosmetic reformat that
 * changes the file without attributing a semantic rule, so the runner has to
 * recognise it as a code-format change.
 *
 * @changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog-11.html
 */
final class NormalizeWhitespaceTextRule implements TextRule
{
    public function apply(File $file): void
    {
        $lines = explode("\n", $file->getContent());
        $trimmedLines = array_map(rtrim(...), $lines);

        $file->changeFileContent(implode("\n", $trimmedLines));
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Trim trailing whitespace from every line', [new CodeSample("a \nb ", "a\nb")]);
    }
}
