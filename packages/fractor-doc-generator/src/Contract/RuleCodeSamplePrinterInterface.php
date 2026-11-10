<?php

declare(strict_types=1);

namespace a9f\FractorDocGenerator\Contract;

use a9f\Fractor\RuleDoc\Contract\CodeSampleInterface;
use a9f\FractorDocGenerator\ValueObject\DocumentedRule;

interface RuleCodeSamplePrinterInterface
{
    public function isMatch(string $class): bool;

    /**
     * @return string[]
     */
    public function print(CodeSampleInterface $codeSample, DocumentedRule $documentedRule): array;
}
