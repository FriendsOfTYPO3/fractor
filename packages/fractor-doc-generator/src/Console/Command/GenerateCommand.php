<?php

declare(strict_types=1);

namespace a9f\FractorDocGenerator\Console\Command;

use a9f\FractorDocGenerator\Printer\DirectoryToMarkdownPrinter;
use a9f\FractorDocGenerator\ValueObject\Option;
use Nette\Utils\FileSystem;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'generate',
    description: 'Generates Markdown documentation based on documented rules found in directory'
)]
final class GenerateCommand extends Command
{
    public function __construct(
        private readonly DirectoryToMarkdownPrinter $directoryToMarkdownPrinter,
        private readonly SymfonyStyle $symfonyStyle,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument(
            Option::PATHS,
            InputArgument::REQUIRED | InputArgument::IS_ARRAY,
            'Path to directory of your project'
        );

        $this->addOption(
            Option::OUTPUT_FILE,
            null,
            InputOption::VALUE_REQUIRED,
            'Path to output generated markdown file',
            getcwd() . '/docs/rules_overview.md'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $paths = (array) $input->getArgument(Option::PATHS);

        $outputFilePath = (string) $input->getOption(Option::OUTPUT_FILE);

        $markdownFileDirectory = dirname($outputFilePath);

        // ensure directory exists
        if (! file_exists($markdownFileDirectory)) {
            FileSystem::createDir($markdownFileDirectory);
        }

        $markdownFileContent = $this->directoryToMarkdownPrinter->print($markdownFileDirectory, $paths);

        FileSystem::write($outputFilePath, $markdownFileContent);

        $this->symfonyStyle->success(sprintf('File "%s" was created', $outputFilePath));

        return self::SUCCESS;
    }
}
