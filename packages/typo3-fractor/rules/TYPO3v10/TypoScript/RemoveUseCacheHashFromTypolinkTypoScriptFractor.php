<?php

declare(strict_types=1);

namespace a9f\Typo3Fractor\TYPO3v10\TypoScript;

use a9f\Fractor\RuleDoc\CodeSample\CodeSample;
use a9f\Fractor\RuleDoc\RuleDefinition;
use a9f\FractorTypoScript\AbstractTypoScriptFractor;
use a9f\FractorTypoScript\TypoScriptStatementsIterator;
use Helmich\TypoScriptParser\Parser\AST\Operator\Assignment;
use Helmich\TypoScriptParser\Parser\AST\Statement;

/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/10.0/Deprecation-88406-SetCacheHashnoCacheHashOptionsInViewHelpersAndUriBuilder.html
 * @see \a9f\Typo3Fractor\Tests\TYPO3v10\TypoScript\RemoveUseCacheHashFromTypolinkTypoScriptFractor\RemoveUseCacheHashFromTypolinkTypoScriptFractorTest
 */
final class RemoveUseCacheHashFromTypolinkTypoScriptFractor extends AbstractTypoScriptFractor
{
    public function refactor(Statement $statement): null|int
    {
        if (! $statement instanceof Assignment) {
            return null;
        }

        // for some weird reason, "foo.bar.baz = 1" leads to "relativeName" being "foo.bar.baz"
        if (! str_ends_with($statement->object->absoluteName, '.typolink.useCacheHash')) {
            return null;
        }

        return TypoScriptStatementsIterator::REMOVE_NODE;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove useCacheHash TypoScript setting', [new CodeSample(
            <<<'CODE_SAMPLE'
typolink {
    parameter = 3
    useCacheHash = 1
}
CODE_SAMPLE
            ,
            <<<'CODE_SAMPLE'
typolink {
    parameter = 3
}
CODE_SAMPLE
        )]);
    }
}
