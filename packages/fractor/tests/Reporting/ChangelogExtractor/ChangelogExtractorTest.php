<?php

declare(strict_types=1);

namespace a9f\Fractor\Tests\Reporting\ChangelogExtractor;

use a9f\Fractor\Application\ValueObject\AppliedRule;
use a9f\Fractor\Reporting\ChangelogExtractor;
use a9f\Fractor\Tests\Reporting\ChangelogExtractor\Fixture\RuleWithChangelog;
use a9f\Fractor\Tests\Reporting\ChangelogExtractor\Fixture\RuleWithNoChangelog;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ChangelogExtractorTest extends TestCase
{
    private ChangelogExtractor $subject;

    protected function setUp(): void
    {
        $this->subject = new ChangelogExtractor();
    }

    #[Test]
    public function ruleWithNoChangelogDocBlock(): void
    {
        // Act & Assert
        self::assertNull($this->subject->extractChangelogFromRule(RuleWithNoChangelog::class));
    }

    #[Test]
    public function virtualCodeFormatRuleIsToleratedWithoutReflection(): void
    {
        // the code-format label has no backing class; it must not be reflected on
        self::assertNull($this->subject->extractChangelogFromRule(AppliedRule::CODE_FORMAT_RULE));
    }

    #[Test]
    public function ruleWithChangelog(): void
    {
        // Act
        $changelog = $this->subject->extractChangelogFromRule(RuleWithChangelog::class);

        // Assert
        self::assertSame(
            'https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/7.6/Deprecation-69822-DeprecateSelectFieldTca.html',
            $changelog
        );
    }
}
