<?php

declare(strict_types=1);

namespace a9f\FractorComposerJson;

use a9f\Fractor\Application\Contract\FileProcessor;
use a9f\Fractor\Application\ValueObject\AppliedRule;
use a9f\Fractor\Application\ValueObject\File;
use a9f\Fractor\Caching\Detector\ChangedFilesDetector;
use a9f\Fractor\ValueObject\Indent;
use a9f\FractorComposerJson\Contract\ComposerJsonFractorRule;
use a9f\FractorComposerJson\Contract\ComposerJsonPrinter;

/**
 * @implements FileProcessor<ComposerJsonFractorRule>
 */
final readonly class ComposerJsonFileProcessor implements FileProcessor
{
    /**
     * @param iterable<ComposerJsonFractorRule> $rules
     */
    public function __construct(
        private iterable $rules,
        private Indent $indent,
        private ComposerJsonPrinter $composerJsonPrinter,
        private ComposerJsonFactory $composerJsonFactory,
        private ChangedFilesDetector $changedFilesDetector
    ) {
    }

    public function canHandle(File $file): bool
    {
        return $file->getFileName() === 'composer.json';
    }

    public function handle(File $file, iterable $appliedRules): void
    {
        $rawContent = $file->getContent();
        $composerJson = $this->composerJsonFactory->createFromFile($file);

        foreach ($appliedRules as $rule) {
            $beforeArray = $composerJson->getJsonArray();
            $rule->refactor($composerJson);

            if ($beforeArray !== $composerJson->getJsonArray()) {
                $file->addAppliedRule(AppliedRule::fromRule($rule));
            }
        }

        // Always re-print: a formatting-only change (indentation) is then
        // attributed to CodeFormatRule by the runner rather than applied silently.
        $newContent = rtrim($this->composerJsonPrinter->printToString($this->indent, $composerJson)) . "\n";

        if ($newContent === $rawContent) {
            $this->changedFilesDetector->addCachableFile($file->getFilePath());
            return;
        }

        $file->changeFileContent($newContent);
    }

    public function allowedFileExtensions(): array
    {
        return ['json'];
    }

    public function getAllRules(): iterable
    {
        return $this->rules;
    }
}
