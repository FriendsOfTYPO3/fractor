<?php

declare(strict_types=1);

namespace a9f\FractorComposerJson;

use a9f\Fractor\Contract\NoChangelogRequired;
use a9f\Fractor\RuleDoc\CodeSample\ConfiguredCodeSample;
use a9f\Fractor\RuleDoc\RuleDefinition;
use a9f\FractorComposerJson\Contract\ComposerJson;
use a9f\FractorComposerJson\Contract\ComposerJsonFractorRule;
use Webmozart\Assert\Assert;

/**
 * @see \a9f\FractorComposerJson\Tests\RemovePackageComposerJsonFractor\RemovePackageComposerJsonFractorTest
 */
final class RemovePackageComposerJsonFractor implements ComposerJsonFractorRule, NoChangelogRequired
{
    /**
     * @var string[]
     */
    private array $packageNames = [];

    public function refactor(ComposerJson $composerJson): void
    {
        foreach ($this->packageNames as $packageName) {
            $composerJson->removePackage($packageName);
        }
    }

    public function configure(array $configuration): void
    {
        Assert::allString($configuration);

        $this->packageNames = $configuration;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove package from "require" and "require-dev" in `composer.json`', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
{
    "require": {
        "symfony/console": "^3.4"
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
{
}
CODE_SAMPLE
                ,
                ['symfony/console']
            ),
        ]);
    }
}
