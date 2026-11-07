<?php

declare(strict_types=1);

namespace a9f\Fractor\RuleDoc\CodeSample;

use a9f\Fractor\RuleDoc\AbstractCodeSample;

/**
 * @api
 */
final class ExtraFileCodeSample extends AbstractCodeSample
{
    public function __construct(
        string $badCode,
        string $goodCode,
        private readonly string $extraFile
    ) {
        parent::__construct($badCode, $goodCode);
    }

    public function getExtraFile(): string
    {
        return $this->extraFile;
    }
}
