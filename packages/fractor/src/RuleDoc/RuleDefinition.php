<?php

declare(strict_types=1);

namespace a9f\Fractor\RuleDoc;

use a9f\Fractor\RuleDoc\CodeSample\ConfiguredCodeSample;
use a9f\Fractor\RuleDoc\Contract\CodeSampleInterface;
use a9f\Fractor\RuleDoc\Exception\PoorDocumentationException;
use a9f\Fractor\RuleDoc\Exception\ShouldNotHappenException;

/**
 * @api
 */
final class RuleDefinition
{
    private ?string $ruleFilePath = null;

    /**
     * @var CodeSampleInterface[]
     */
    private readonly array $codeSamples;

    /**
     * @param CodeSampleInterface[] $codeSamples
     */
    public function __construct(
        private readonly string $description,
        array $codeSamples
    ) {
        if ($codeSamples === []) {
            throw new PoorDocumentationException(
                'Provide at least one code sample, so people can practically see what the rule does'
            );
        }

        $this->codeSamples = $codeSamples;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setRuleFilePath(string $ruleFilePath): void
    {
        // relative file path for GitHub
        $this->ruleFilePath = ltrim($ruleFilePath, '/');
    }

    public function getRuleFilePath(): string
    {
        if ($this->ruleFilePath === null) {
            throw new ShouldNotHappenException();
        }

        return $this->ruleFilePath;
    }

    /**
     * @return CodeSampleInterface[]
     */
    public function getCodeSamples(): array
    {
        return $this->codeSamples;
    }

    public function isConfigurable(): bool
    {
        foreach ($this->codeSamples as $codeSample) {
            if ($codeSample instanceof ConfiguredCodeSample) {
                return true;
            }
        }

        return false;
    }
}
