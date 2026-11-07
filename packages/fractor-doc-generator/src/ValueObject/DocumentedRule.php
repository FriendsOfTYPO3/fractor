<?php

declare(strict_types=1);

namespace a9f\FractorDocGenerator\ValueObject;

use a9f\Fractor\RuleDoc\RuleDefinition;

final readonly class DocumentedRule
{
    public function __construct(
        private string $ruleClass,
        private RuleDefinition $ruleDefinition
    ) {
    }

    public function getRuleClass(): string
    {
        return $this->ruleClass;
    }

    public function getRuleShortClass(): string
    {
        return basename(str_replace('\\', '/', $this->ruleClass));
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return $this->ruleDefinition;
    }
}
