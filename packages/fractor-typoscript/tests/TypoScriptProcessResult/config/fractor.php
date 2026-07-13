<?php

declare(strict_types=1);

use a9f\Fractor\Configuration\FractorConfiguration;
use a9f\FractorTypoScript\Configuration\TypoScriptProcessorOption;
use a9f\FractorTypoScript\Tests\Fixtures\DummyTypoScriptFractorRule;
use Helmich\TypoScriptParser\Parser\Printer\PrettyPrinterConditionTermination;
use Helmich\TypoScriptParser\Parser\Printer\PrettyPrinterConfiguration;

return FractorConfiguration::configure()
    ->withOptions([
        TypoScriptProcessorOption::INDENT_SIZE => 4,
        TypoScriptProcessorOption::INDENT_CHARACTER => PrettyPrinterConfiguration::INDENTATION_STYLE_SPACES,
        TypoScriptProcessorOption::ADD_CLOSING_GLOBAL => false,
        TypoScriptProcessorOption::INCLUDE_EMPTY_LINE_BREAKS => true,
        TypoScriptProcessorOption::INDENT_CONDITIONS => true,
        TypoScriptProcessorOption::CONDITION_TERMINATION => PrettyPrinterConditionTermination::Keep,
    ])
    ->withRules([DummyTypoScriptFractorRule::class]);
