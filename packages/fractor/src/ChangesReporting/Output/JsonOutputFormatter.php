<?php

declare(strict_types=1);

namespace a9f\Fractor\ChangesReporting\Output;

use a9f\Fractor\ChangesReporting\Contract\Output\OutputFormatterInterface;
use a9f\Fractor\Configuration\ValueObject\Configuration;
use a9f\Fractor\ValueObject\ProcessResult;
use Nette\Utils\Json;

final class JsonOutputFormatter implements OutputFormatterInterface
{
    /**
     * @var string
     */
    public const NAME = 'json';

    public function getName(): string
    {
        return self::NAME;
    }

    public function report(ProcessResult $processResult, Configuration $configuration): void
    {
        $systemErrors = $processResult->getSystemErrors();

        $errorsJson = [
            'totals' => [
                'changed_files' => count($processResult->getFileDiffs()),
                'errors' => count($systemErrors),
            ],
        ];

        foreach ($systemErrors as $systemError) {
            $errorsJson['errors'][] = [
                'message' => $systemError->getMessage(),
                'file' => $systemError->getRelativeFilePath(),
                'caused_by' => $systemError->getProcessorClass(),
            ];
        }

        $fileDiffs = $processResult->getFileDiffs();
        ksort($fileDiffs);
        foreach ($fileDiffs as $fileDiff) {
            $filePath = $fileDiff->getRelativeFilePath();

            if ($configuration->shouldShowDiffs() && $fileDiff->getDiff() !== '') {
                $errorsJson['file_diffs'][] = [
                    'file' => $filePath,
                    'diff' => $fileDiff->getDiff(),
                    'applied_rules' => $configuration->shouldShowChangelog() ? $fileDiff->getChangelogsLines() : $fileDiff->getFractorClasses(),
                ];
            }

            // for CI
            $errorsJson['changed_files'][] = $filePath;
        }

        $json = Json::encode($errorsJson, pretty: true);
        echo $json . PHP_EOL;
    }
}
