<?php

declare(strict_types=1);

namespace a9f\Typo3Fractor\TYPO3v12\Fluid;

use a9f\Fractor\RuleDoc\CodeSample\CodeSample;
use a9f\Fractor\RuleDoc\RuleDefinition;
use a9f\FractorFluid\Contract\FluidFractorRule;
use Nette\Utils\Strings;

/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/12.0/Breaking-97787-AbstractMessageGetSeverityReturnsContextualFeedbackSeverity.html
 * @see \a9f\Typo3Fractor\Tests\TYPO3v12\Fluid\AbstractMessageGetSeverityFluidFractor\AbstractMessageGetSeverityFluidFractorTest
 */
final class AbstractMessageGetSeverityFluidFractor implements FluidFractorRule
{
    /**
     * @var string
     */
    private const PATTERN = '#{status.severity}#imsU';

    /**
     * @var string
     */
    private const REPLACEMENT = '{status.severity.value}';

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition("Migrate to severity property 'value'", [
            new CodeSample(
                <<<'CODE_SAMPLE'
<div class="{severityClassMapping.{status.severity}}">
    <!-- stuff happens here -->
</div>
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
<div class="{severityClassMapping.{status.severity.value}}">
    <!-- stuff happens here -->
</div>
CODE_SAMPLE
            ),
        ]);
    }

    public function refactor(string $fluid): string
    {
        return Strings::replace($fluid, self::PATTERN, self::REPLACEMENT);
    }
}
