<?php

declare(strict_types=1);

namespace a9f\Typo3Fractor\TYPO3v12\TypoScript;

use a9f\Fractor\RuleDoc\CodeSample\CodeSample;
use a9f\Fractor\RuleDoc\RuleDefinition;
use a9f\FractorTypoScript\AbstractTypoScriptFractor;
use Helmich\TypoScriptParser\Parser\AST\Operator\Assignment;
use Helmich\TypoScriptParser\Parser\AST\Statement;

/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/12.4/Deprecation-100461-TypoScriptOptionConfigxhtmlDoctype.html
 */
final class RenameConfigXhtmlDoctypeToDoctypeFractor extends AbstractTypoScriptFractor
{
    public function refactor(Statement $statement): null|Statement
    {
        if (! $statement instanceof Assignment) {
            return null;
        }

        if ($statement->object->absoluteName !== 'config.xhtmlDoctype') {
            return null;
        }

        $statement->object->relativeName = str_replace('xhtmlDoctype', 'doctype', $statement->object->relativeName);
        $statement->object->absoluteName = str_replace('xhtmlDoctype', 'doctype', $statement->object->absoluteName);

        return $statement;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Migrate typoscript xhtmlDoctype to doctype', [new CodeSample(
            <<<'CODE_SAMPLE'
config.xhtmlDoctype = 1
CODE_SAMPLE
            ,
            <<<'CODE_SAMPLE'
config.doctype = 1
CODE_SAMPLE
        )]);
    }
}
