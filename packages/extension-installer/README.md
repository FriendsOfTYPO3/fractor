# Fractor Extension Installer

Composer plugin for automatic installation of Fractor extensions.

## Usage

```bash
composer require --dev a9f/fractor-extension-installer
```

## Instructions for extension developers

It's best to set the extension's composer package [type](https://getcomposer.org/doc/04-schema.md#type) to `fractor-extension` for this plugin to be able to recognize it and to be [discoverable on Packagist](https://packagist.org/explore/?type=fractor-extension).

Add `fractor` key in the extension `composer.json`'s `extra` section:

```json
{
    "extra": {
        "fractor": {
            "includes": [
                "config/config.php"
            ]
        }
    }
}
```

## Limitations

The extension installer depends on Composer script events, therefore you cannot use `--no-scripts` flag.

## Acknowledgment

This package is heavily inspired by [phpstan/extension-installer](https://github.com/phpstan/extension-installer) by Ondřej Mirtes. Thank you.

## Development

Development happens in the [Fractor monorepo on GitHub](https://github.com/friendsoftypo3/fractor/)

