<?php

declare(strict_types=1);

namespace a9f\Typo3Fractor\TYPO3v12\TypoScript;

use a9f\Fractor\RuleDoc\CodeSample\CodeSample;
use a9f\Fractor\RuleDoc\RuleDefinition;
use a9f\FractorTypoScript\AbstractTypoScriptFractor;
use a9f\FractorTypoScript\TypoScriptStatementsIterator;
use Helmich\TypoScriptParser\Parser\AST\Operator\Assignment;
use Helmich\TypoScriptParser\Parser\AST\Statement;

/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/12.0/Breaking-98437-WorkspaceTSConfigSwapModeAndChangeStageModeRemoved.html
 */
final class RemoveWorkspaceModeOptionsFractor extends AbstractTypoScriptFractor
{
    public function refactor(Statement $statement): null|int
    {
        if (! $statement instanceof Assignment) {
            return null;
        }

        if ($statement->object->absoluteName !== 'options.workspaces.swapMode'
            && $statement->object->absoluteName !== 'options.workspaces.changeStageMode'
        ) {
            return null;
        }

        return TypoScriptStatementsIterator::REMOVE_NODE;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Remove TSConfig options.workspaces.swapMode and options.workspaces.changeStageMode',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
options.workspaces.swapMode = any
options.workspaces.changeStageMode = any
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
-
CODE_SAMPLE
                ),
            ]
        );
    }
}
