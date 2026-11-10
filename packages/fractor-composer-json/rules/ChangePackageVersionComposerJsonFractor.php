<?php

declare(strict_types=1);

namespace a9f\FractorComposerJson;

use a9f\Fractor\Contract\NoChangelogRequired;
use a9f\Fractor\RuleDoc\CodeSample\ConfiguredCodeSample;
use a9f\Fractor\RuleDoc\RuleDefinition;
use a9f\FractorComposerJson\Contract\ComposerJson;
use a9f\FractorComposerJson\Contract\ComposerJsonFractorRule;
use a9f\FractorComposerJson\ValueObject\PackageAndVersion;
use Webmozart\Assert\Assert;

/**
 * @see \a9f\FractorComposerJson\Tests\ChangePackageVersionComposerJsonFractor\ChangePackageVersionComposerJsonFractorTest
 */
final class ChangePackageVersionComposerJsonFractor implements ComposerJsonFractorRule, NoChangelogRequired
{
    /**
     * @var PackageAndVersion[]
     */
    private array $packagesAndVersions = [];

    public function refactor(ComposerJson $composerJson): void
    {
        foreach ($this->packagesAndVersions as $packageAndVersion) {
            $composerJson->changePackageVersion($packageAndVersion);
        }
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change package version `composer.json`', [new ConfiguredCodeSample(
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
    "require": {
        "symfony/console": "^4.4"
    }
}
CODE_SAMPLE
            ,
            [new PackageAndVersion('symfony/console', '^4.4')]
        ),
        ]);
    }

    public function configure(array $configuration): void
    {
        Assert::allIsInstanceOf($configuration, PackageAndVersion::class);

        $this->packagesAndVersions = $configuration;
    }
}
