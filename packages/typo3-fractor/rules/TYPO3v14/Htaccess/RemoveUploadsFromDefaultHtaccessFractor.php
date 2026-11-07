<?php

declare(strict_types=1);

namespace a9f\Typo3Fractor\TYPO3v14\Htaccess;

use a9f\Fractor\RuleDoc\CodeSample\CodeSample;
use a9f\Fractor\RuleDoc\RuleDefinition;
use a9f\FractorHtaccess\Contract\HtaccessFractorRule;
use Tivie\HtaccessParser\HtaccessContainer;
use Tivie\HtaccessParser\Token\Block;
use Tivie\HtaccessParser\Token\Directive;
use Tivie\HtaccessParser\Token\TokenInterface;
use const Tivie\HtaccessParser\Token\TOKEN_DIRECTIVE;

/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/14.0/Important-105244-UpdatedDefaultHtaccessTemplate.html
 * @see \a9f\Typo3Fractor\Tests\TYPO3v14\Htaccess\RemoveUploadsFromDefaultHtaccessFractor\RemoveUploadsFromDefaultHtaccessFractorTest
 */
final class RemoveUploadsFromDefaultHtaccessFractor implements HtaccessFractorRule
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove uploads/ from default .htaccess and add _assets/', [new CodeSample(
            <<<'CODE_SAMPLE'
RewriteRule ^(?:fileadmin/|typo3conf/|typo3temp/|uploads/) - [L]
CODE_SAMPLE
            ,
            <<<'CODE_SAMPLE'
RewriteRule ^(?:fileadmin/|typo3conf/|typo3temp/|_assets/) - [L]
CODE_SAMPLE
        )]);
    }

    public function refactor(HtaccessContainer $node): HtaccessContainer
    {
        $this->changeDirectivesRecursively($node, 'RewriteRule', TOKEN_DIRECTIVE);
        return $node;
    }

    /**
     * @param (HtaccessContainer<int, TokenInterface>|Block<int, TokenInterface>)&\ArrayAccess<int, TokenInterface> $container
     */
    private function changeDirectivesRecursively(HtaccessContainer|Block $container, string $name, int $type): void
    {
        foreach ($container as $key => $token) {
            if (! $token instanceof TokenInterface) {
                continue;
            }

            if ($token->getTokenType() === $type
                && fnmatch($name, $token->getName())
                && str_contains($token->getArguments()[0] ?? '', 'uploads/')
            ) {
                $arguments = $token->getArguments();
                $arguments[0] = str_replace('uploads/', '_assets/', $token->getArguments()[0]);
                $container->offsetSet($key, new Directive($token->getName(), $arguments));
            } elseif ($token instanceof Block && $token->hasChildren()) {
                $this->changeDirectivesRecursively($token, $name, $type);
            }
        }
    }
}
