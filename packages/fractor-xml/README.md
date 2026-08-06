# Fractor for XML files

Fractor extension package with a file processor for XML files.

This uses PHP's libxml integration and provides a full traversal of the DOM Node tree.

## Installation

```bash
composer require a9f/fractor-xml --dev
```

## Configuration

All rules must implement the a9f\FractorXml\Contract\XmlFractor interface.
The rule will be tagged with 'fractor.xml_rule' and be injected in the XmlFileProcessor.

## Development

Development happens in the [Fractor monorepo on GitHub](https://github.com/friendsoftypo3/fractor/)
