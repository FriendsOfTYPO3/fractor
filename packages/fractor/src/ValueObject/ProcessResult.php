<?php

declare(strict_types=1);

namespace a9f\Fractor\ValueObject;

use a9f\Fractor\Application\Contract\FractorRule;
use a9f\Fractor\Differ\ValueObject\FileDiff;
use Webmozart\Assert\Assert;

final readonly class ProcessResult
{
    /**
     * @param FileDiff[] $fileDiffs
     */
    public function __construct(
        private array $fileDiffs,
        private int $totalChanged,
    ) {
        Assert::allIsInstanceOf($this->fileDiffs, FileDiff::class);
    }

    /**
     * @return FileDiff[]
     */
    public function getFileDiffs(bool $onlyWithChanges = true): array
    {
        if ($onlyWithChanges) {
            return array_filter($this->fileDiffs, static fn (FileDiff $fileDiff): bool => $fileDiff->getDiff() !== '');
        }
        return $this->fileDiffs;
    }

    public function getTotalChanged(): int
    {
        return $this->totalChanged;
    }

    /**
     * @return array<class-string<FractorRule>, int>
     */
    public function getRuleApplicationCounts(): array
    {
        $ruleCounts = [];

        foreach ($this->fileDiffs as $fileDiff) {
            foreach ($fileDiff->getFractorClasses() as $fractorClass) {
                $ruleCounts[$fractorClass] ??= 0;

                ++$ruleCounts[$fractorClass];
            }
        }

        arsort($ruleCounts);
        return $ruleCounts;
    }
}
