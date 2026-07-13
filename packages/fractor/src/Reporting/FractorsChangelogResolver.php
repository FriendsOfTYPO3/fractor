<?php

declare(strict_types=1);

namespace a9f\Fractor\Reporting;

use a9f\Fractor\Application\Contract\FractorRule;
use a9f\Fractor\Application\ValueObject\AppliedRule;

final readonly class FractorsChangelogResolver
{
    public function __construct(
        private ChangelogExtractor $changelogExtractor
    ) {
    }

    /**
     * @param AppliedRule[] $appliedRules
     * @return array<class-string<FractorRule>|AppliedRule::CODE_FORMAT_RULE, string|null>
     */
    public function resolveIncludingMissing(array $appliedRules): array
    {
        $fractorClassesToChangelogUrls = [];
        foreach ($appliedRules as $appliedRule) {
            $fractorClass = $appliedRule->getFractorClass();
            $changelogUrl = $this->changelogExtractor->extractChangelogFromRule($fractorClass);
            $fractorClassesToChangelogUrls[$fractorClass] = $changelogUrl;
        }

        return $fractorClassesToChangelogUrls;
    }
}
