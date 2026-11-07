<?php

declare(strict_types=1);

namespace a9f\Typo3Fractor\TYPO3v14\Fluid;

use a9f\Fractor\RuleDoc\CodeSample\CodeSample;
use a9f\Fractor\RuleDoc\RuleDefinition;
use a9f\FractorFluid\Contract\FluidFractorRule;
use Nette\Utils\Strings;

/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/14.0/Breaking-103910-ChangeLogoutHandlingInExtfelogin.html
 * @see \a9f\Typo3Fractor\Tests\TYPO3v14\Fluid\ChangeLogoutHandlingInFeLoginFractor\ChangeLogoutHandlingInFeLoginFractorTest
 */
final class ChangeLogoutHandlingInFeLoginFractor implements FluidFractorRule
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change logout handling in ext:felogin', [new CodeSample(
            <<<'CODE_SAMPLE'
<f:form action="login" actionUri="{actionUri}" target="_top" fieldNamePrefix="">
CODE_SAMPLE
            ,
            <<<'CODE_SAMPLE'
<f:form action="login" target="_top" fieldNamePrefix="">
CODE_SAMPLE
        ), new CodeSample(
            <<<'CODE_SAMPLE'
<div class="felogin-hidden">
    <f:form.hidden name="logintype" value="logout"/>
</div>
CODE_SAMPLE
            ,
            <<<'CODE_SAMPLE'
<div class="felogin-hidden">
    <f:form.hidden name="logintype" value="logout"/>
    <f:if condition="{noRedirect} != ''">
        <f:form.hidden name="noredirect" value="1" />
    </f:if>
</div>
CODE_SAMPLE
        )]);
    }

    public function refactor(string $fluid): string
    {
        $fluid = Strings::replace($fluid, '# actionUri="\{actionUri}"#imsU', '');

        if (! str_contains($fluid, '<f:form.hidden name="noredirect" value="1"')) {
            $html = <<<HTML
<f:form.hidden name="logintype" value="logout"/>
        <f:if condition="{noRedirect} != ''">
            <f:form.hidden name="noredirect" value="1" />
        </f:if>
HTML;
            $fluid = str_replace('<f:form.hidden name="logintype" value="logout"/>', $html, $fluid);
        }

        return $fluid;
    }
}
