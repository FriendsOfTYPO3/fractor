<?php

declare(strict_types=1);

namespace a9f\Fractor\Console\Command;

use a9f\Fractor\Application\FractorRunner;
use a9f\Fractor\ChangesReporting\Output\ConsoleOutputFormatter;
use a9f\Fractor\Configuration\ConfigInitializer;
use a9f\Fractor\Configuration\ConfigurationFactory;
use a9f\Fractor\Configuration\ConfigurationRuleFilter;
use a9f\Fractor\Configuration\Option;
use a9f\Fractor\Configuration\ValueObject\Configuration;
use a9f\Fractor\Console\Application\FractorApplication;
use a9f\Fractor\Console\ExitCode;
use a9f\Fractor\Console\Output\OutputFormatterCollector;
use a9f\Fractor\Exception\ShouldNotHappenException;
use a9f\Fractor\Util\MemoryLimiter;
use a9f\Fractor\ValueObject\ProcessResult;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'process', description: 'Runs Fractor with the given configuration file')]
final class ProcessCommand extends Command
{
    public function __construct(
        private readonly FractorRunner $runner,
        private readonly ConfigurationFactory $configurationFactory,
        private readonly OutputFormatterCollector $outputFormatterCollector,
        private readonly ConfigInitializer $configInitializer,
        private readonly MemoryLimiter $memoryLimiter,
        private readonly ConfigurationRuleFilter $configurationRuleFilter
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument(
            Option::SOURCE,
            InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
            'Files or directories to be upgraded.'
        );

        $this->addOption(
            Option::CONFIG,
            Option::CONFIG_SHORT,
            InputOption::VALUE_REQUIRED,
            'The configuration file to use. Default: fractor.php in the current directory.'
        );

        $this->addOption(
            Option::DRY_RUN,
            Option::DRY_RUN_SHORT,
            InputOption::VALUE_NONE,
            'Only see the diff of changes, do not save them to files.'
        );
        $this->addOption(
            Option::NO_PROGRESS_BAR,
            null,
            InputOption::VALUE_NONE,
            'Hide progress bar. Useful e.g. for nicer CI output.'
        );
        $this->addOption(
            Option::NO_DIFFS,
            null,
            InputOption::VALUE_NONE,
            'Hide diffs of changed files. Useful e.g. for nicer CI output.'
        );
        $this->addOption(
            Option::SHOW_CHANGELOG,
            null,
            InputOption::VALUE_NONE,
            'Show changelog URI. Useful e.g. for more verbose output.'
        );
        $this->addOption(Option::MEMORY_LIMIT, null, InputOption::VALUE_REQUIRED, 'Memory limit for process');
        $this->addOption(Option::CLEAR_CACHE, null, InputOption::VALUE_NONE, 'Clear unchanged files cache');

        $this->addOption(
            Option::OUTPUT_FORMAT,
            null,
            InputOption::VALUE_REQUIRED,
            'Select output format',
            ConsoleOutputFormatter::NAME
        );

        // filter by rule and path
        $this->addOption(Option::ONLY, null, InputOption::VALUE_REQUIRED, 'Fully qualified rule class name');

        $this->addOption(
            Option::RULES_SUMMARY,
            null,
            InputOption::VALUE_NONE,
            'Show summary of rules applied during the run.'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // missing config? add it :)
        if (! $this->configInitializer->areSomeFractorsLoaded()) {
            $this->configInitializer->createConfig((string) getcwd());
            return self::SUCCESS;
        }

        $configuration = $this->configurationFactory->createFromInput($input);

        $this->memoryLimiter->adjust($configuration);
        $this->configurationRuleFilter->setConfiguration($configuration);

        $processResult = $this->runner->run($configuration);

        $outputFormat = $configuration->getOutputFormat();
        $outputFormatter = $this->outputFormatterCollector->getByName($outputFormat);

        $outputFormatter->report($processResult, $configuration);

        return $this->resolveReturnCode($processResult, $configuration);
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $application = $this->getApplication();
        if (! $application instanceof FractorApplication) {
            throw new ShouldNotHappenException();
        }
    }

    /**
     * @return ExitCode::*
     */
    private function resolveReturnCode(ProcessResult $processResult, Configuration $configuration): int
    {
        // some system errors were found -> fail
        if ($processResult->getSystemErrors() !== []) {
            return ExitCode::FAILURE;
        }

        // inverse error code for CI dry-run
        if (! $configuration->isDryRun()) {
            return ExitCode::SUCCESS;
        }
        if ($processResult->getFileDiffs() !== []) {
            return ExitCode::CHANGED_CODE;
        }
        return ExitCode::SUCCESS;
    }
}
