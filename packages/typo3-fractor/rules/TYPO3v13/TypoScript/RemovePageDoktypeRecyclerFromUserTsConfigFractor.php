<?php

declare(strict_types=1);

namespace a9f\Typo3Fractor\TYPO3v13\TypoScript;

use a9f\Fractor\Helper\ArrayUtility;
use a9f\Fractor\RuleDoc\CodeSample\CodeSample;
use a9f\Fractor\RuleDoc\RuleDefinition;
use a9f\FractorTypoScript\AbstractTypoScriptFractor;
use Helmich\TypoScriptParser\Parser\AST\Operator\Assignment;
use Helmich\TypoScriptParser\Parser\AST\Statement;

/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/13.0/Breaking-101137-PageDoktypeRecyclerRemoved.html
 * @see \a9f\Typo3Fractor\Tests\TYPO3v13\TypoScript\RemovePageDoktypeRecyclerFromUserTsConfigFractor\RemovePageDoktypeRecyclerFromUserTsConfigFractorTest
 */
final class RemovePageDoktypeRecyclerFromUserTsConfigFractor extends AbstractTypoScriptFractor
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove Page Doktype Recycler (255) from User Tsconfig', [new CodeSample(
            <<<'CODE_SAMPLE'
options.pageTree {
    doktypesToShowInNewPageDragArea = 1,6,4,7,3,254,255,199
}
CODE_SAMPLE
            ,
            <<<'CODE_SAMPLE'
options.pageTree {
    doktypesToShowInNewPageDragArea = 1,6,4,7,3,254,199
}
CODE_SAMPLE
        )]);
    }

    public function refactor(Statement $statement): null|Statement
    {
        if ($this->shouldSkip($statement)) {
            return null;
        }

        /** @var Assignment $statement */
        $values = ArrayUtility::trimExplode(',', $statement->value->value);
        $values = array_diff($values, [255]);
        $statement->value->value = implode(',', $values);

        return $statement;
    }

    private function shouldSkip(Statement $statement): bool
    {
        if (! $statement instanceof Assignment) {
            return true;
        }

        if ($statement->object->absoluteName !== 'options.pageTree.doktypesToShowInNewPageDragArea') {
            return true;
        }

        return false;
    }
}
