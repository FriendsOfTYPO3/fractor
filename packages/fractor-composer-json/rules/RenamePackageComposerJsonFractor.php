<?php

declare(strict_types=1);

namespace a9f\FractorComposerJson;

use a9f\Fractor\Contract\NoChangelogRequired;
use a9f\Fractor\RuleDoc\CodeSample\ConfiguredCodeSample;
use a9f\Fractor\RuleDoc\RuleDefinition;
use a9f\FractorComposerJson\Contract\ComposerJson;
use a9f\FractorComposerJson\Contract\ComposerJsonFractorRule;
use a9f\FractorComposerJson\ValueObject\RenamePackage;
use Webmozart\Assert\Assert;

/**
 * @see \a9f\FractorComposerJson\Tests\RenamePackageComposerJsonFractor\RenamePackageComposerJsonFractorTest
 */
final class RenamePackageComposerJsonFractor implements ComposerJsonFractorRule, NoChangelogRequired
{
    /**
     * @var RenamePackage[]
     */
    private array $renamePackages = [];

    public function refactor(ComposerJson $composerJson): void
    {
        foreach ($this->renamePackages as $renamePackage) {
            if ($composerJson->hasRequiredPackage($renamePackage->getOldPackageName())) {
                $composerJson->replaceRequiredPackage($renamePackage);
            }

            if ($composerJson->hasRequiredDevPackage($renamePackage->getOldPackageName())) {
                $composerJson->replaceRequiredDevPackage($renamePackage);
            }
        }
    }

    public function configure(array $configuration): void
    {
        Assert::allIsInstanceOf($configuration, RenamePackage::class);
        $this->renamePackages = $configuration;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change package name in `composer.json`', [new ConfiguredCodeSample(
            <<<'CODE_SAMPLE'
{
    "require": {
        "rector/rector": "dev-main"
    }
}
CODE_SAMPLE
            ,
            <<<'CODE_SAMPLE'
{
    "require": {
        "rector/rector-src": "dev-main"
    }
}
CODE_SAMPLE
            ,
            [new RenamePackage('rector/rector', 'rector/rector-src')]
        ),
        ]);
    }
}
