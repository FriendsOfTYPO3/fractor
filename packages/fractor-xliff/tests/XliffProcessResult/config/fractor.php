<?php

declare(strict_types=1);

use a9f\Fractor\Configuration\FractorConfiguration;
use a9f\Fractor\ValueObject\Indent;
use a9f\FractorXliff\Configuration\XliffProcessorOption;
use a9f\FractorXliff\Tests\Fixtures\DummyXliffFractorRule;

return FractorConfiguration::configure()
    ->withOptions([
        XliffProcessorOption::INDENT_CHARACTER => Indent::STYLE_TAB,
        XliffProcessorOption::INDENT_SIZE => 1,
    ])
    ->withRules([DummyXliffFractorRule::class]);
