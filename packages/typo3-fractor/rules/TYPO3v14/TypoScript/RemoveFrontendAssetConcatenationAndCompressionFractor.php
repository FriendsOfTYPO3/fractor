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
 * @changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/14.0/Breaking-108055-RemovedFrontendAssetConcatenationAndCompression.html
 * @see \a9f\Typo3Fractor\Tests\TYPO3v14\TypoScript\RemoveFrontendAssetConcatenationAndCompressionFractor\RemoveFrontendAssetConcatenationAndCompressionFractorTest
 */
final class RemoveFrontendAssetConcatenationAndCompressionFractor extends AbstractTypoScriptFractor
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove Frontend Asset Concatenation and Compression', [new CodeSample(
            <<<'CODE_SAMPLE'
config.compressCss
config.compressJs
config.concatenateCss
config.concatenateJs
CODE_SAMPLE
            ,
            <<<'CODE_SAMPLE'
-
-
-
-
CODE_SAMPLE
        ), new CodeSample(
            <<<'CODE_SAMPLE'
page = PAGE
page.includeCSS {
    main = EXT:site_package/Resources/Public/Css/main.css
    main.disableCompression = 1
    main.excludeFromConcatenation = 1
}
CODE_SAMPLE
            ,
            <<<'CODE_SAMPLE'
page = PAGE
page.includeCSS {
    main = EXT:site_package/Resources/Public/Css/main.css
}
CODE_SAMPLE
        )]);
    }

    public function refactor(Statement $statement): null|int
    {
        if (! $statement instanceof Assignment) {
            return null;
        }

        $absoluteName = $statement->object->absoluteName;

        // check if specific global config options are matched
        if (in_array($absoluteName, $this->getFullOptionNames(), true)) {
            return TypoScriptStatementsIterator::REMOVE_NODE;
        }

        $relativeName = $statement->object->relativeName;

        // check if the property ends with one of the deprecated property names
        foreach ($this->getSuffixesToRemove() as $suffix) {
            if (str_ends_with($relativeName, $suffix)) {
                return TypoScriptStatementsIterator::REMOVE_NODE;
            }
        }

        return null;
    }

    /**
     * @return string[]
     */
    private function getFullOptionNames(): array
    {
        return ['config.compressCss', 'config.compressJs', 'config.concatenateCss', 'config.concatenateJs'];
    }

    /**
     * @return string[]
     */
    private function getSuffixesToRemove(): array
    {
        return ['.disableCompression', '.excludeFromConcatenation'];
    }
}
