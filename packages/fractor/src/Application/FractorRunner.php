<?php

declare(strict_types=1);

namespace a9f\Fractor\Application;

use a9f\Fractor\Application\Contract\FileProcessor;
use a9f\Fractor\Application\Contract\FileWriter;
use a9f\Fractor\Application\Contract\FractorRule;
use a9f\Fractor\Application\ValueObject\File;
use a9f\Fractor\Caching\Detector\ChangedFilesDetector;
use a9f\Fractor\Configuration\ConfigurationRuleFilter;
use a9f\Fractor\Configuration\ValueObject\Configuration;
use a9f\Fractor\Differ\ValueObject\FileDiff;
use a9f\Fractor\Differ\ValueObjectFactory\FileDiffFactory;
use a9f\Fractor\FileSystem\FilePathHelper;
use a9f\Fractor\FileSystem\FilesFinder;
use a9f\Fractor\ValueObject\Error\SystemError;
use a9f\Fractor\ValueObject\FileProcessResult;
use a9f\Fractor\ValueObject\ProcessResult;
use Nette\Utils\FileSystem;
use Symfony\Component\Console\Style\SymfonyStyle;
use Webmozart\Assert\Assert;

/**
 * Main Fractor class. This takes care of collecting a list of files, iterating over them and calling all registered
 * processors for them.
 */
final readonly class FractorRunner
{
    /**
     * @param iterable<FileProcessor<FractorRule>> $processors
     */
    public function __construct(
        private SymfonyStyle $symfonyStyle,
        private FilesFinder $fileFinder,
        private FilesCollector $fileCollector,
        private iterable $processors,
        private FileWriter $fileWriter,
        private FileDiffFactory $fileDiffFactory,
        private RuleSkipper $ruleSkipper,
        private ProcessorSkipper $processorSkipper,
        private ChangedFilesDetector $changedFilesDetector,
        private ConfigurationRuleFilter $configurationRuleFilter,
        private FilePathHelper $filePathHelper
    ) {
        Assert::allIsInstanceOf($this->processors, FileProcessor::class);
    }

    public function run(Configuration $configuration): ProcessResult
    {
        $filePaths = $this->fileFinder->findFilesInPaths($configuration->getPaths(), $configuration);

        // no files found
        if ($filePaths === []) {
            return new ProcessResult([], 0);
        }

        $shouldShowProgressBar = $configuration->shouldShowProgressBar();

        if ($shouldShowProgressBar) {
            $this->symfonyStyle->progressStart(count($filePaths));
            $this->symfonyStyle->progressAdvance(0);
        }

        /** @var FileDiff[] $fileDiffs */
        $fileDiffs = [];

        /** @var SystemError[] $systemErrors */
        $systemErrors = [];

        $totalChanged = 0;
        foreach ($filePaths as $filePath) {
            $file = new File($filePath, FileSystem::read($filePath));
            $this->fileCollector->addFile($file);

            if ($shouldShowProgressBar) {
                $this->symfonyStyle->progressAdvance();
            }

            $systemError = $this->processFile($file);
            if ($systemError instanceof SystemError) {
                // The file never receives a diff below, so the writer skips it
                // and a half-processed document cannot reach the disk.
                $systemErrors[] = $systemError;
                continue;
            }

            if (! $file->hasChanged()) {
                $this->changedFilesDetector->cacheFile($file->getFilePath());
                continue;
            }

            ++$totalChanged;

            $file->setFileDiff($this->fileDiffFactory->createFileDiff($configuration->shouldShowDiffs(), $file));

            $fileProcessResult = new FileProcessResult($file->getFileDiff());
            $currentFileDiff = $fileProcessResult->getFileDiff();
            if ($currentFileDiff instanceof FileDiff) {
                $fileDiffs[] = $currentFileDiff;
            }
        }

        foreach ($this->fileCollector->getFiles() as $file) {
            if ($file->getFileDiff() === null) {
                continue;
            }

            if ($configuration->isDryRun()) {
                continue;
            }

            $this->fileWriter->write($file);
        }

        return new ProcessResult($fileDiffs, $totalChanged, $systemErrors);
    }

    /**
     * Runs every applicable processor for a single file.
     *
     * A processor that fails must not take the whole run down with it, so the
     * failure is turned into a reportable error naming the file, and the file
     * itself is left untouched.
     */
    private function processFile(File $file): ?SystemError
    {
        foreach ($this->processors as $processor) {
            if ($this->processorSkipper->shouldSkip($processor::class)) {
                continue;
            }

            if (! $processor->canHandle($file)) {
                continue;
            }

            $applicableRules = $this->filterApplicableRules($processor->getAllRules(), $file);

            try {
                $processor->handle($file, $applicableRules);
            } catch (\Throwable $throwable) {
                return new SystemError(
                    $throwable->getMessage(),
                    $this->filePathHelper->relativePath($file->getFilePath()),
                    $processor::class
                );
            }
        }

        return null;
    }

    /**
     * @param iterable<FractorRule> $rules
     * @return \Generator<FractorRule>
     */
    private function filterApplicableRules(iterable $rules, File $file): \Generator
    {
        $rules = $this->configurationRuleFilter->filter($rules);

        foreach ($rules as $rule) {
            if ($this->ruleSkipper->shouldSkip($rule::class, $file->getFilePath())) {
                continue;
            }

            yield $rule;
        }
    }
}
