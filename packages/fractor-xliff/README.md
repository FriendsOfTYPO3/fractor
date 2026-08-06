# Fractor XLIFF

XLIFF extension for the [Fractor](https://github.com/friendsoftypo3/fractor) file refactoring tool.

Allows validating and transforming XLIFF (XML Localization Interchange File Format) translation files.
Supports XLIFF Versions 1.0, 1.1, 1.2 and 2.0.

## Installation

```bash
composer require a9f/fractor-xliff --dev
```

## Configuration

```php
<?php

use a9f\Fractor\Configuration\FractorConfiguration;
use a9f\FractorXliff\Configuration\XliffProcessorOption;
use a9f\Fractor\ValueObject\Indent;

return FractorConfiguration::configure()
    ->withPaths([__DIR__ . '/Resources/Private/Language/'])
    ->withOptions([
        XliffProcessorOption::INDENT_CHARACTER => Indent::STYLE_SPACE,
        XliffProcessorOption::INDENT_SIZE => 4,
        XliffProcessorOption::ALLOWED_FILE_EXTENSIONS => ['xlf', 'xliff'],
    ]);
```

Have a look at all available rules [Overview of all rules](docs/xliff-fractor-rules.md)

## Processed File Extensions

By default, the following file extensions are processed: `xlf`, `xliff`.

## For Devlopers

All rules must implement the a9f\FractorXliff\Contract\XliffFractorRule interface.
The rule will be tagged with 'fractor.xliff_rule' and be injected in the XliffFileProcessor.
