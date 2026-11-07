<?php

declare(strict_types=1);

namespace a9f\Typo3Fractor;

use a9f\Fractor\RuleDoc\CodeSample\CodeSample;
use a9f\Fractor\RuleDoc\RuleDefinition;
use a9f\FractorTypoScript\AbstractTypoScriptFractor;
use a9f\FractorTypoScript\TypoScriptStatementsIterator;
use Helmich\TypoScriptParser\Parser\AST\Operator\Assignment;
use Helmich\TypoScriptParser\Parser\AST\Statement;

/**
 * Base class for Fractors that remove a deprecated/removed global TypoScript setting. Primary use case is the
 * global "config" object, but other globally scoped objects like PageTSconfig or UserTSconfig can be modified as well.
 *
 * This should not be used if an option within a TS function or object was removed, e.g. within typolink or the
 * FLUIDTEMPLATE object.
 */
abstract class AbstractRemoveTypoScriptSettingFractor extends AbstractTypoScriptFractor
{
    final public function refactor(Statement $statement): null|int
    {
        if (! $statement instanceof Assignment) {
            return null;
        }

        if ($statement->object->absoluteName !== $this->getFullOptionName()) {
            return null;
        }

        return TypoScriptStatementsIterator::REMOVE_NODE;
    }

    final public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(sprintf('Remove %s', $this->getFullOptionName()), [new CodeSample(
            sprintf(<<<'CODE_SAMPLE'
%s = 1
CODE_SAMPLE
                , $this->getFullOptionName()),
            <<<'CODE_SAMPLE'
-
CODE_SAMPLE
        )]);
    }

    abstract protected function getFullOptionName(): string;
}
