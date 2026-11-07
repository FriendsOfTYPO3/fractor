<?php

declare(strict_types=1);

namespace a9f\FractorDocGenerator;

use a9f\FractorDocGenerator\Exception\ShouldNotHappenException;
use a9f\FractorDocGenerator\ValueObject\DocumentedRule;
use a9f\FractorDocGenerator\ValueObject\RuleClassWithFilePath;
use ReflectionClass;
use Symplify\RuleDocGenerator\Contract\DocumentedRuleInterface;

final class RuleDefinitionsResolver
{
    /**
     * @param RuleClassWithFilePath[] $ruleClassWithFilePaths
     * @return DocumentedRule[]
     */
    public function resolveFromClassNames(array $ruleClassWithFilePaths): array
    {
        $documentedRules = [];

        foreach ($ruleClassWithFilePaths as $ruleClassWithFilePath) {
            $reflectionClass = new ReflectionClass($ruleClassWithFilePath->getClass());

            $documentedRule = $reflectionClass->newInstanceWithoutConstructor();
            if (! $documentedRule instanceof DocumentedRuleInterface) {
                throw new ShouldNotHappenException();
            }

            $ruleDefinition = $documentedRule->getRuleDefinition();
            $ruleDefinition->setRuleFilePath($ruleClassWithFilePath->getPath());

            $documentedRules[] = new DocumentedRule($ruleClassWithFilePath->getClass(), $ruleDefinition);
        }

        usort(
            $documentedRules,
            static fn (DocumentedRule $first, DocumentedRule $second): int => $first->getRuleShortClass() <=> $second->getRuleShortClass()
        );

        return $documentedRules;
    }
}
