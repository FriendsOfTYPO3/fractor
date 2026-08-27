<?php

declare(strict_types=1);

namespace a9f\Fractor\Testing\PHPUnit\ValueObject;

use a9f\Fractor\Application\Contract\FractorRule;
use a9f\Fractor\ValueObject\Error\SystemError;
use a9f\Fractor\ValueObject\ProcessResult;

final readonly class FractorTestResult
{
    public function __construct(
        private string $changedContents,
        private ProcessResult $processResult
    ) {
    }

    public function getChangedContents(): string
    {
        return $this->changedContents;
    }

    /**
     * @return SystemError[]
     */
    public function getSystemErrors(): array
    {
        return $this->processResult->getSystemErrors();
    }

    /**
     * @return array<class-string<FractorRule>>
     */
    public function getAppliedFractorRules(): array
    {
        $appliedRules = [];
        foreach ($this->processResult->getFileDiffs() as $fileDiff) {
            $appliedRules = \array_merge($appliedRules, $fileDiff->getFractorClasses());
        }
        \sort($appliedRules);
        return \array_unique($appliedRules);
    }
}
