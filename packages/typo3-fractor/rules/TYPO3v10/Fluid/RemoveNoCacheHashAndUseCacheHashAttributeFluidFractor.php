<?php

declare(strict_types=1);

namespace a9f\Typo3Fractor\TYPO3v10\Fluid;

use a9f\Fractor\RuleDoc\CodeSample\CodeSample;
use a9f\Fractor\RuleDoc\RuleDefinition;
use a9f\FractorFluid\Contract\FluidFractorRule;
use Nette\Utils\Strings;

/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/10.0/Deprecation-88406-SetCacheHashnoCacheHashOptionsInViewHelpersAndUriBuilder.html
 * @see \a9f\Typo3Fractor\Tests\TYPO3v10\Fluid\RemoveNoCacheHashAndUseCacheHashAttributeFluidFractor\RemoveNoCacheHashAndUseCacheHashAttributeFractorTest
 */
final class RemoveNoCacheHashAndUseCacheHashAttributeFluidFractor implements FluidFractorRule
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove noCacheHash="1" and useCacheHash="1" attribute', [
            new CodeSample(
                <<<'CODE_SAMPLE'
<f:link.page noCacheHash="1">Link</f:link.page>
<f:link.typolink useCacheHash="1">Link</f:link.typolink>
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
<f:link.page>Link</f:link.page>
<f:link.typolink>Link</f:link.typolink>
CODE_SAMPLE
            ),
        ]);
    }

    public function refactor(string $fluid): string
    {
        $fluid = Strings::replace($fluid, '# noCacheHash="(1|0|true|false)"#imsU', '');

        return Strings::replace($fluid, '# useCacheHash="(1|0|true|false)"#imsU', '');
    }
}
