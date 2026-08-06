# Fractor for Fluid Templates

Fractor extension package with a file processor for Fluid templates.

## Installation

```bash
composer require a9f/fractor-fluid --dev
```

## Configuration

All rules must implement the a9f\FractorFluid\Contract\FluidFractorRule interface.
The rule will be tagged with 'fractor.fluid_rule' and be injected in the FluidFileProcessor.

## Development

Development happens in the [Fractor monorepo on GitHub](https://github.com/friendsoftypo3/fractor/)
