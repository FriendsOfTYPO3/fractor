<?php

declare(strict_types=1);

namespace a9f\Fractor\Application\ValueObject;

use a9f\Fractor\Application\Contract\FractorRule;

final readonly class AppliedRule
{
    /**
     * Identifier for the virtual code-formatting rule, which has no backing class.
     */
    public const CODE_FORMAT_RULE = 'CodeFormatRule';

    /**
     * @param class-string<FractorRule>|self::CODE_FORMAT_RULE $fractorClass
     */
    private function __construct(
        private string $fractorClass,
    ) {
    }

    public static function fromRule(FractorRule $fractorRule): self
    {
        return new self($fractorRule::class);
    }

    /**
     * @param class-string<FractorRule> $fractorRule
     */
    public static function fromClassString(string $fractorRule): self
    {
        return new self($fractorRule);
    }

    public static function codeFormat(): self
    {
        return new self(self::CODE_FORMAT_RULE);
    }

    /**
     * @return class-string<FractorRule>|self::CODE_FORMAT_RULE
     */
    public function getFractorClass(): string
    {
        return $this->fractorClass;
    }

    public function isCodeFormat(): bool
    {
        return $this->fractorClass === self::CODE_FORMAT_RULE;
    }
}
