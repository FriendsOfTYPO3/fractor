<?php

declare(strict_types=1);

namespace a9f\Fractor\Tests\Application\ValueObject\AppliedRule;

use a9f\Fractor\Application\ValueObject\AppliedRule;
use a9f\Fractor\Tests\Reporting\ChangelogExtractor\Fixture\RuleWithNoChangelog;
use PHPUnit\Framework\TestCase;

final class AppliedRuleTest extends TestCase
{
    public function testCodeFormatFactoryProducesTheVirtualRule(): void
    {
        $appliedRule = AppliedRule::codeFormat();

        self::assertSame(AppliedRule::CODE_FORMAT_RULE, $appliedRule->getFractorClass());
        self::assertTrue($appliedRule->isCodeFormat());
    }

    public function testRealRuleIsNotCodeFormat(): void
    {
        $appliedRule = AppliedRule::fromClassString(RuleWithNoChangelog::class);

        self::assertFalse($appliedRule->isCodeFormat());
    }
}
