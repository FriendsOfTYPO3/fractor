<?php

declare(strict_types=1);

namespace a9f\Fractor\RuleDoc\Contract;

interface CodeSampleInterface
{
    public function getGoodCode(): string;

    public function getBadCode(): string;
}
