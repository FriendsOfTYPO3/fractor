<?php

declare(strict_types=1);

namespace a9f\Typo3Fractor\TYPO3v8\Fluid;

use a9f\Fractor\RuleDoc\CodeSample\CodeSample;
use a9f\Fractor\RuleDoc\RuleDefinition;
use a9f\FractorFluid\Contract\FluidFractorRule;
use Nette\Utils\Strings;

/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/8.0/Breaking-69863-ChangesInViewHelpersPostFluidStandalone.html
 * @see \a9f\Typo3Fractor\Tests\TYPO3v8\Fluid\ReplaceCaseDefaultWithDefaultCaseFluidFractor\ReplaceCaseDefaultWithDefaultCaseFluidFractorTest
 */
final class ReplaceCaseDefaultWithDefaultCaseFluidFractor implements FluidFractorRule
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replace f:case with default attribute by f:defaultCase', [new CodeSample(
            <<<'CODE_SAMPLE'
<f:switch expression="{someVariable}">
    <f:case value="foo">foo</f:case>
    <f:case default="true">bar</f:case>
</f:switch>
CODE_SAMPLE
            ,
            <<<'CODE_SAMPLE'
<f:switch expression="{someVariable}">
    <f:case value="foo">foo</f:case>
    <f:defaultCase>bar</f:defaultCase>
</f:switch>
CODE_SAMPLE
        )]);
    }

    public function refactor(string $fluid): string
    {
        // Replace opening + closing tag with content
        $fluid = Strings::replace(
            $fluid,
            '#<f:case\s+default="(true|1)"\s*>(.*?)</f:case>#si',
            '<f:defaultCase>$2</f:defaultCase>'
        );

        // Replace self-closing tag
        return Strings::replace($fluid, '#<f:case\s+default="(true|1)"\s*/>#si', '<f:defaultCase />');
    }
}
