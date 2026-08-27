<?php

declare(strict_types=1);

namespace a9f\Fractor\Console;

use Symfony\Component\Console\Command\Command;

final class ExitCode
{
    public const SUCCESS = Command::SUCCESS;

    /**
     * @var int
     */
    public const CHANGED_CODE = 2;

    /**
     * @var int
     */
    public const FAILURE = Command::FAILURE;
}
