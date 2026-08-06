# Fractor for composer.json files

Fractor extension package with a file processor for composer.json files.

## Installation

```bash
composer require a9f/fractor-composer-json --dev
```

## Configuration

All rules must implement the a9f\FractorComposerJson\Contract\ComposerJsonFractorRule interface.
The rule will be tagged with 'fractor.composer_json_rule' and be injected in the ComposerJsonFileProcessor.

Have a look at all available rules [Overview of all rules](docs/composer-json-fractor-rules.md)

## Development

Development happens in the [Fractor monorepo on GitHub](https://github.com/friendsoftypo3/fractor/)
