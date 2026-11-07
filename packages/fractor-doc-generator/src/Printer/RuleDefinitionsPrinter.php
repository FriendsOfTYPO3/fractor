<?php

declare(strict_types=1);

namespace a9f\FractorDocGenerator\Printer;

use a9f\FractorDocGenerator\Text\KeywordHighlighter;
use a9f\FractorDocGenerator\ValueObject\DocumentedRule;

final readonly class RuleDefinitionsPrinter
{
    public function __construct(
        private CodeSamplePrinter $codeSamplePrinter,
        private KeywordHighlighter $keywordHighlighter,
    ) {
    }

    /**
     * @param DocumentedRule[] $documentedRules
     * @return string[]
     */
    public function print(array $documentedRules): array
    {
        $ruleCount = count($documentedRules);

        $lines = [];
        $lines[] = sprintf('# %d Rules Overview', $ruleCount);

        return $this->printRuleDefinitions($documentedRules, $lines);
    }

    /**
     * @param DocumentedRule[] $documentedRules
     * @param string[] $lines
     * @return string[]
     */
    private function printRuleDefinitions(array $documentedRules, array $lines): array
    {
        foreach ($documentedRules as $documentedRule) {
            $ruleDefinition = $documentedRule->getRuleDefinition();

            $lines[] = '## ' . $documentedRule->getRuleShortClass();

            $lines[] = $this->keywordHighlighter->highlight($ruleDefinition->getDescription());

            if ($ruleDefinition->isConfigurable()) {
                $lines[] = ':wrench: **configure it!**';
            }

            $lines[] = '- class: [`' . $documentedRule->getRuleClass() . '`](' . $ruleDefinition->getRuleFilePath() . ')';

            $codeSampleLines = $this->codeSamplePrinter->print($documentedRule);
            $lines = array_merge($lines, $codeSampleLines);
        }

        return $lines;
    }
}
