<?php

declare(strict_types=1);

namespace a9f\Fractor\Console\Style;

use a9f\Fractor\Util\Reflection\PrivatesAccessor;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

final readonly class SymfonyStyleFactory
{
    public function __construct(
        private PrivatesAccessor $privatesAccessor
    ) {
    }

    public function create(): FractorStyle
    {
        $_SERVER['argv'] ??= [];

        $argvInput = new ArgvInput();
        $consoleOutput = new ConsoleOutput();

        // to configure all -v, -vv, -vvv options without memory-lock to Application run() arguments
        $this->privatesAccessor->callPrivateMethod(new Application(), 'configureIO', [$argvInput, $consoleOutput]);

        // --debug is called
        if ($argvInput->hasParameterOption('--debug')) {
            $consoleOutput->setVerbosity(OutputInterface::VERBOSITY_DEBUG);
        }

        // disable output for tests
        if ($this->isPHPUnitRun()) {
            $consoleOutput->setVerbosity(OutputInterface::VERBOSITY_QUIET);
        }

        return new FractorStyle($argvInput, $consoleOutput);
    }

    /**
     * Never ever used static methods if not necessary, this is just handy for tests + src to prevent duplication.
     */
    private function isPHPUnitRun(): bool
    {
        return defined('PHPUNIT_COMPOSER_INSTALL') || defined('__PHPUNIT_PHAR__');
    }
}
