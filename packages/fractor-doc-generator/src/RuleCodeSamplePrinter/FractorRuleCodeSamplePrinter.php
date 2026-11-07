<?php

declare(strict_types=1);

namespace a9f\FractorDocGenerator\RuleCodeSamplePrinter;

use a9f\Fractor\Application\Contract\FractorRule;
use a9f\Fractor\RuleDoc\Contract\CodeSampleInterface;
use a9f\FractorDocGenerator\Contract\RuleCodeSamplePrinterInterface;
use a9f\FractorDocGenerator\Printer\DiffCodeSamplePrinter;
use a9f\FractorDocGenerator\ValueObject\DocumentedRule;

final readonly class FractorRuleCodeSamplePrinter implements RuleCodeSamplePrinterInterface
{
    public function __construct(
        private DiffCodeSamplePrinter $diffCodeSamplePrinter,
    ) {
    }

    public function isMatch(string $class): bool
    {
        return is_a($class, FractorRule::class, true);
    }

    public function print(CodeSampleInterface $codeSample, DocumentedRule $documentedRule): array
    {
        return $this->diffCodeSamplePrinter->print($codeSample);
    }
}
