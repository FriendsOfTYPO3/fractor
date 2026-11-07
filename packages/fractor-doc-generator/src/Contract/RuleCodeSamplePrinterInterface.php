<?php

declare(strict_types=1);

namespace a9f\FractorDocGenerator\Contract;

use a9f\FractorDocGenerator\ValueObject\DocumentedRule;
use Symplify\RuleDocGenerator\Contract\CodeSampleInterface;

interface RuleCodeSamplePrinterInterface
{
    public function isMatch(string $class): bool;

    /**
     * @return string[]
     */
    public function print(CodeSampleInterface $codeSample, DocumentedRule $documentedRule): array;
}
