<?php

declare(strict_types=1);

namespace a9f\FractorHtaccess\Tests\Fixtures;

use a9f\Fractor\RuleDoc\RuleDefinition;
use a9f\FractorHtaccess\Contract\HtaccessFractorRule;
use Symfony\Component\DependencyInjection\Exception\BadMethodCallException;
use Tivie\HtaccessParser\HtaccessContainer;
use Tivie\HtaccessParser\Token\Block;
use Tivie\HtaccessParser\Token\TokenInterface;
use const Tivie\HtaccessParser\Token\TOKEN_DIRECTIVE;

/**
 * This is a demo rule for testing purpose
 */
final class RemoveSetEnvIfRule implements HtaccessFractorRule
{
    public function getRuleDefinition(): RuleDefinition
    {
        throw new BadMethodCallException('Not implemented yet');
    }

    public function refactor(HtaccessContainer $node): HtaccessContainer
    {
        $this->removeDirectivesRecursively($node, 'SetEnvIf', TOKEN_DIRECTIVE);
        return $node;
    }

    /**
     * Recursively searches for and removes specified directives within a container or block.
     */
    private function removeDirectivesRecursively(HtaccessContainer|Block $container, string $name, int $type): void
    {
        $keysToRemove = [];

        foreach ($container as $key => $token) {
            if (! $token instanceof TokenInterface) {
                continue;
            }

            if ($token->getTokenType() === $type && fnmatch($name, $token->getName())) {
                $keysToRemove[] = $key;
            } elseif ($token instanceof Block && $token->hasChildren()) {
                $this->removeDirectivesRecursively($token, $name, $type);
            }
        }

        rsort($keysToRemove, SORT_NUMERIC);

        foreach ($keysToRemove as $key) {
            if ($container->offsetExists($key)) {
                $container->offsetUnset($key);
            }
        }
    }
}
