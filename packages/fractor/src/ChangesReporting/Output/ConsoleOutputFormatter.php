<?php

declare(strict_types=1);

namespace a9f\Fractor\ChangesReporting\Output;

use a9f\Fractor\ChangesReporting\Contract\Output\OutputFormatterInterface;
use a9f\Fractor\Configuration\ValueObject\Configuration;
use a9f\Fractor\Differ\ValueObject\FileDiff;
use a9f\Fractor\ValueObject\Error\SystemError;
use a9f\Fractor\ValueObject\ProcessResult;
use Nette\Utils\Strings;
use Symfony\Component\Console\Style\SymfonyStyle;

final readonly class ConsoleOutputFormatter implements OutputFormatterInterface
{
    /**
     * @var string
     */
    public const NAME = 'console';

    public function __construct(
        private SymfonyStyle $symfonyStyle
    ) {
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function report(ProcessResult $processResult, Configuration $configuration): void
    {
        if ($configuration->shouldShowDiffs()) {
            $this->reportFileDiffs($processResult->getFileDiffs(), false, $configuration->shouldShowChangelog());
        }

        $this->reportErrors($processResult->getSystemErrors());

        // a failed run reports the errors instead of a success message
        if ($processResult->getSystemErrors() !== []) {
            return;
        }

        // to keep space between progress bar and success message
        if ($configuration->shouldShowProgressBar() && $processResult->getFileDiffs() === []) {
            $this->symfonyStyle->newLine();
        }

        if ($configuration->shouldShowRulesSummary()) {
            $this->reportRulesSummary($processResult, $configuration);
        }

        $message = $this->createSuccessMessage($processResult, $configuration);
        $this->symfonyStyle->success($message);
    }

    /**
     * @param FileDiff[] $fileDiffs
     */
    private function reportFileDiffs(array $fileDiffs, bool $absoluteFilePath, bool $showChangelog): void
    {
        if (\count($fileDiffs) <= 0) {
            return;
        }
        // normalize
        \ksort($fileDiffs);
        $message = \sprintf('%d file%s with changes', \count($fileDiffs), \count($fileDiffs) === 1 ? '' : 's');
        $this->symfonyStyle->title($message);

        $i = 0;
        foreach ($fileDiffs as $fileDiff) {
            $filePath = $absoluteFilePath ? $fileDiff->getAbsoluteFilePath() ?? '' : $fileDiff->getRelativeFilePath();
            // append line number for faster file jump in diff
            $firstLineNumber = $fileDiff->getFirstLineNumber();
            if ($firstLineNumber !== null) {
                $filePath .= ':' . $firstLineNumber;
            }
            $message = \sprintf('<options=bold>%d) %s</>', ++$i, $filePath);
            $this->symfonyStyle->writeln($message);
            $this->symfonyStyle->newLine();
            $this->symfonyStyle->writeln($fileDiff->getDiffConsoleFormatted());

            if ($fileDiff->getAppliedRules() !== []) {
                $this->symfonyStyle->writeln('<options=underscore>Applied rules:</>');
                $this->symfonyStyle->listing(
                    $showChangelog ? $fileDiff->getChangelogsLines() : $fileDiff->getFractorShortClasses()
                );
                $this->symfonyStyle->newLine();
            }
        }
    }

    /**
     * @param SystemError[] $systemErrors
     */
    private function reportErrors(array $systemErrors): void
    {
        foreach ($systemErrors as $systemError) {
            $message = \sprintf(
                'Could not process "%s" file by "%s", due to: %s"%s".',
                $systemError->getRelativeFilePath(),
                $systemError->getProcessorShortClass(),
                "\n",
                $systemError->getMessage()
            );

            $this->symfonyStyle->error($message);
        }
    }

    private function createSuccessMessage(ProcessResult $processResult, Configuration $configuration): string
    {
        $changeCount = $processResult->getTotalChanged();
        if ($changeCount === 0) {
            return 'Fractor is done!';
        }
        return \sprintf(
            '%d file%s %s by Fractor',
            $changeCount,
            $changeCount > 1 ? 's' : '',
            $configuration->isDryRun()
                ? 'would have been changed (dry-run)'
                : ($changeCount === 1 ? 'has' : 'have') . ' been changed'
        );
    }

    private function reportRulesSummary(ProcessResult $processResult, Configuration $configuration): void
    {
        $ruleApplicationCounts = $processResult->getRuleApplicationCounts();
        if ($ruleApplicationCounts === []) {
            return;
        }

        $verb = $configuration->isDryRun() ? 'would have been applied' : 'was applied';

        $this->symfonyStyle->section('Rules Summary');

        foreach ($ruleApplicationCounts as $ruleClass => $count) {
            $ruleShortClass = (string) Strings::after($ruleClass, '\\', -1);
            $this->symfonyStyle->writeln(sprintf(
                ' * <info>%s</info> %s <comment>%d</comment> time%s',
                $ruleShortClass,
                $verb,
                $count,
                $count > 1 ? 's' : ''
            ));
        }

        $this->symfonyStyle->newLine();
    }
}
