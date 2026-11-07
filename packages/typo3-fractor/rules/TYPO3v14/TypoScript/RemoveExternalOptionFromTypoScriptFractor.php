<?php

declare(strict_types=1);

namespace a9f\Typo3Fractor\TYPO3v14\TypoScript;

use a9f\Fractor\RuleDoc\CodeSample\CodeSample;
use a9f\Fractor\RuleDoc\RuleDefinition;
use a9f\FractorTypoScript\AbstractTypoScriptFractor;
use a9f\FractorTypoScript\TypoScriptStatementsIterator;
use Helmich\TypoScriptParser\Parser\AST\Operator\Assignment;
use Helmich\TypoScriptParser\Parser\AST\Statement;

/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/14.0/Breaking-107927-ExternalAttributesRemoved.html
 * @see \a9f\Typo3Fractor\Tests\TYPO3v14\TypoScript\RemoveExternalOptionFromTypoScriptFractor\RemoveExternalOptionFromTypoScriptFractorTest
 */
final class RemoveExternalOptionFromTypoScriptFractor extends AbstractTypoScriptFractor
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove "external" option from TypoScript', [new CodeSample(
            <<<'CODE_SAMPLE'
page = PAGE
page.includeCSS {
    main = https://example.com/styles/main.css
    main.external = 1
    other = /styles/main.css
    other.external = 1
}
CODE_SAMPLE
            ,
            <<<'CODE_SAMPLE'
page = PAGE
page.includeCSS {
    main = https://example.com/styles/main.css
    other = URI:/styles/main.css
}
CODE_SAMPLE
        )]);
    }

    public function refactor(Statement $statement): null|Statement|int
    {
        if (! $statement instanceof Assignment) {
            return null;
        }

        if (! str_contains($statement->object->absoluteName, 'includeCSS')
            && ! str_contains($statement->object->absoluteName, 'includeCSSLibs')
            && ! str_contains($statement->object->absoluteName, 'includeJS')
            && ! str_contains($statement->object->absoluteName, 'includeJSFooter')
            && ! str_contains($statement->object->absoluteName, 'includeJSFooterlibs')
            && ! str_contains($statement->object->absoluteName, 'includeJSLibs')
        ) {
            return null;
        }

        if (str_ends_with($statement->object->relativeName, 'external')) {
            return TypoScriptStatementsIterator::REMOVE_NODE;
        }

        if (str_starts_with($statement->value->value, '/')) {
            $statement->value->value = 'URI:' . $statement->value->value;
            return $statement;
        }

        return null;
    }
}
