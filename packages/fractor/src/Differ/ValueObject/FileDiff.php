<?php

declare(strict_types=1);

namespace a9f\Fractor\Differ\ValueObject;

use a9f\Fractor\Application\Contract\FractorRule;
use a9f\Fractor\Application\ValueObject\AppliedRule;
use Nette\Utils\Strings;
use Webmozart\Assert\Assert;

final readonly class FileDiff
{
    /**
     * @var string
     */
    private const FIRST_LINE_KEY = 'first_line';

    /**
     * @var string
     * @se https://regex101.com/r/AUPIX4/1
     */
    private const FIRST_LINE_REGEX = '#@@(.*?)(?<' . self::FIRST_LINE_KEY . '>\\d+)(.*?)@@#';

    /**
     * @param AppliedRule[] $appliedRules
     * @param string[] $changelogsLines
     */
    public function __construct(
        private string $relativeFilePath,
        private string $diff,
        private string $diffConsoleFormatted,
        private array $appliedRules = [],
        private array $changelogsLines = [],
    ) {
        Assert::allIsInstanceOf($appliedRules, AppliedRule::class);
    }

    public function getRelativeFilePath(): string
    {
        return $this->relativeFilePath;
    }

    public function getAbsoluteFilePath(): ?string
    {
        return \realpath($this->relativeFilePath) ?: null;
    }

    public function getDiff(): string
    {
        return $this->diff;
    }

    public function getDiffConsoleFormatted(): string
    {
        return $this->diffConsoleFormatted;
    }

    /**
     * @return AppliedRule[]
     */
    public function getAppliedRules(): array
    {
        return $this->appliedRules;
    }

    /**
     * @return string[]
     */
    public function getChangelogsLines(): array
    {
        return $this->changelogsLines;
    }

    /**
     * @return string[]
     */
    public function getFractorShortClasses(): array
    {
        $fractorShortClasses = [];
        foreach ($this->getFractorClasses() as $fractorClass) {
            // fall back to the full identifier for labels without a namespace (e.g. the code-format rule)
            $fractorShortClasses[] = Strings::after($fractorClass, '\\', -1) ?? $fractorClass;
        }
        return $fractorShortClasses;
    }

    /**
     * @return array<class-string<FractorRule>|AppliedRule::CODE_FORMAT_RULE>
     */
    public function getFractorClasses(): array
    {
        $fractorClasses = [];
        foreach ($this->appliedRules as $appliedRule) {
            $fractorClasses[] = $appliedRule->getFractorClass();
        }
        $fractorClasses = array_unique($fractorClasses);
        sort($fractorClasses);
        return $fractorClasses;
    }

    public function isCodeFormatOnly(): bool
    {
        if ($this->appliedRules === []) {
            return false;
        }
        foreach ($this->appliedRules as $appliedRule) {
            if (! $appliedRule->isCodeFormat()) {
                return false;
            }
        }
        return true;
    }

    public function getFirstLineNumber(): ?int
    {
        $match = Strings::match($this->diff, self::FIRST_LINE_REGEX);
        // probably some error in diff
        if (! isset($match[self::FIRST_LINE_KEY])) {
            return null;
        }
        return (int) $match[self::FIRST_LINE_KEY];
    }
}
