<?php

declare(strict_types=1);

namespace a9f\Fractor\Tests\Fixture\DummyProcessor\Rules;

use a9f\Fractor\Application\ValueObject\File;
use a9f\Fractor\RuleDoc\CodeSample\CodeSample;
use a9f\Fractor\RuleDoc\RuleDefinition;
use a9f\Fractor\Tests\Fixture\DummyProcessor\Contract\TextRule;

/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog-11.html
 */
final class ReplaceXXXTextRule implements TextRule
{
    public function apply(File $file): void
    {
        $newFileContent = str_replace('XXX', 'YYY', $file->getContent());

        $file->changeFileContent($newFileContent);
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replace string XXX with YYY', [new CodeSample('XXX', 'YYY')]);
    }
}
