<?php

declare(strict_types=1);

namespace a9f\FractorDocGenerator\Printer;

use a9f\Fractor\RuleDoc\CodeSample\ConfiguredCodeSample;
use a9f\Fractor\RuleDoc\Contract\CodeSampleInterface;
use a9f\Fractor\RuleDoc\Contract\ConfigurableRuleInterface;
use a9f\FractorDocGenerator\Contract\RuleCodeSamplePrinterInterface;
use a9f\FractorDocGenerator\Exception\ConfigurationBoundException;
use a9f\FractorDocGenerator\ValueObject\DocumentedRule;

final readonly class CodeSamplePrinter
{
    /**
     * @param RuleCodeSamplePrinterInterface[] $ruleCodeSamplePrinters
     */
    public function __construct(
        private iterable $ruleCodeSamplePrinters
    ) {
    }

    /**
     * @return string[]
     */
    public function print(DocumentedRule $documentedRule): array
    {
        $lines = [];

        foreach ($documentedRule->getRuleDefinition()->getCodeSamples() as $codeSample) {
            $this->ensureConfigureRuleBoundsConfiguredCodeSample($codeSample, $documentedRule);

            foreach ($this->ruleCodeSamplePrinters as $ruleCodeSamplePrinter) {
                if (! $ruleCodeSamplePrinter->isMatch($documentedRule->getRuleClass())) {
                    continue;
                }

                $newLines = $ruleCodeSamplePrinter->print($codeSample, $documentedRule);
                $lines = array_merge($lines, $newLines);
                break;
            }

            $lines[] = '<br>';
        }

        return $lines;
    }

    private function ensureConfigureRuleBoundsConfiguredCodeSample(
        CodeSampleInterface $codeSample,
        DocumentedRule $documentedRule
    ): void {
        // ensure the configured rule + configure code sample are used
        if ($codeSample instanceof ConfiguredCodeSample) {
            if (is_a($documentedRule->getRuleClass(), ConfigurableRuleInterface::class, true)) {
                return;
            }

            $errorMessage = sprintf(
                'The "%s" rule has configure code sample and must implements "%s" interface',
                $documentedRule->getRuleClass(),
                ConfigurableRuleInterface::class
            );
            throw new ConfigurationBoundException($errorMessage);
        }

        if (! is_a($documentedRule->getRuleClass(), ConfigurableRuleInterface::class, true)) {
            return;
        }

        $errorMessage = sprintf(
            'The "%s" rule implements "%s" and code sample must be "%s"',
            $documentedRule->getRuleClass(),
            ConfigurableRuleInterface::class,
            ConfiguredCodeSample::class,
        );
        throw new ConfigurationBoundException($errorMessage);
    }
}
