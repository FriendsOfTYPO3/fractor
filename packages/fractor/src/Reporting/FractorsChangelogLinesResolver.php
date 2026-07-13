<?php

declare(strict_types=1);

namespace a9f\Fractor\Reporting;

use a9f\Fractor\Application\ValueObject\AppliedRule;
use Nette\Utils\Strings;

final readonly class FractorsChangelogLinesResolver
{
    public function __construct(
        private FractorsChangelogResolver $fractorsChangelogResolver
    ) {
    }

    /**
     * @param AppliedRule[] $appliedRules
     * @return string[]
     */
    public function createFractorChangelogLines(array $appliedRules): array
    {
        $rectorsChangelogs = $this->fractorsChangelogResolver->resolveIncludingMissing($appliedRules);

        $fractorsChangelogsLines = [];
        foreach ($rectorsChangelogs as $fractorClass => $changelog) {
            $fractorShortClass = Strings::after($fractorClass, '\\', -1) ?? $fractorClass;
            $fractorsChangelogsLines[] = $changelog === null ? $fractorShortClass : $fractorShortClass . ' (' . $changelog . ')';
        }

        return $fractorsChangelogsLines;
    }
}
