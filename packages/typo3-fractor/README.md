# TYPO3 Fractor

Ease your TYPO3 upgrades by also automatically changing TypoScript, YAML and Fluid.
An enhancement for [TYPO3-Rector](https://github.com/sabbelasichon/typo3-rector).

> [!WARNING]
> :heavy_exclamation_mark: Never run this tool on production! Always run it on development environment where code is under version control (e.g. git).
> Review and test changes before releasing to production. Code migrations could potentially break your website!

## Installation

Install TYPO3 Fractor via composer by running the following command in your terminal:

```bash
composer require a9f/typo3-fractor --dev
```

## Configuration

Create a PHP configuration file `fractor.php` where you define the paths to your files and the rules to apply.

```php
<?php

use a9f\Fractor\Configuration\FractorConfiguration;
use a9f\Typo3Fractor\Set\Typo3LevelSetList;

return FractorConfiguration::configure()
    ->withPaths([__DIR__ . '/packages/'])
    ->withSets([
        Typo3LevelSetList::UP_TO_TYPO3_14
    ]);
```

Have a look at all available rules: [Overview of all rules](docs/typo3-fractor-rules.md)

## Usage

To see the code migrations that Fractor will do, run:

```bash
vendor/bin/fractor process --dry-run
```

and when you want to execute the migrations run:

```bash
vendor/bin/fractor process
```

Fractor will apply the rules specified in the configuration file to the targeted files.

Review the changes to ensure they meet your expectations.

## Development

Development happens in the [Fractor monorepo on GitHub](https://github.com/friendsoftypo3/fractor/)
