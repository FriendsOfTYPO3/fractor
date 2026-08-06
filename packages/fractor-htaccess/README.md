# Fractor for htaccess files

Fractor extension package with a file processor for htaccess files.

## Installation

```bash
composer require a9f/fractor-htaccess --dev
```

## Configuration

All rules must implement the a9f\FractorHtaccess\Contract\HtaccessFractorRule interface.
The rule will be tagged with 'fractor.htaccess_rule' and be injected in the HtaccessFileProcessor.

## Development

Development happens in the [Fractor monorepo on GitHub](https://github.com/friendsoftypo3/fractor/)
