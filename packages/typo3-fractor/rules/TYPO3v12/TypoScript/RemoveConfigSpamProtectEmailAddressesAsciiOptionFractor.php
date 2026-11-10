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
 * @changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/12.0/Breaking-90044-ConfigspamProtectEmailAddressesWithOptionAsciiRemoved.html
 */
final class RemoveConfigSpamProtectEmailAddressesAsciiOptionFractor extends AbstractTypoScriptFractor
{
    public function refactor(Statement $statement): null|int
    {
        if (! $statement instanceof Assignment) {
            return null;
        }

        if ($statement->object->absoluteName !== 'config.spamProtectEmailAddresses'
            || $statement->value->value !== 'ascii'
        ) {
            return null;
        }

        return TypoScriptStatementsIterator::REMOVE_NODE;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove config.spamProtectEmailAddresses with option ascii', [new CodeSample(
            <<<'CODE_SAMPLE'
config.spamProtectEmailAddresses = ascii
CODE_SAMPLE
            ,
            <<<'CODE_SAMPLE'
-
CODE_SAMPLE
        )]);
    }
}
