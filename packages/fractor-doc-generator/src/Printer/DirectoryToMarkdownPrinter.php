<?php

declare(strict_types=1);

namespace a9f\FractorDocGenerator\Printer;

use a9f\Fractor\RuleDoc\Contract\DocumentedRuleInterface;
use a9f\FractorDocGenerator\FileSystem\ClassByTypeFinder;
use a9f\FractorDocGenerator\RuleDefinitionsResolver;
use a9f\FractorDocGenerator\ValueObject\RuleClassWithFilePath;
use Symfony\Component\Console\Style\SymfonyStyle;

final readonly class DirectoryToMarkdownPrinter
{
    public function __construct(
        private ClassByTypeFinder $classByTypeFinder,
        private SymfonyStyle $symfonyStyle,
        private RuleDefinitionsResolver $ruleDefinitionsResolver,
        private RuleDefinitionsPrinter $ruleDefinitionsPrinter,
    ) {
    }

    /**
     * @param string[] $directories
     */
    public function print(string $workingDirectory, array $directories): string
    {
        $documentedRuleClasses = $this->classByTypeFinder->findByType(
            $workingDirectory,
            $directories,
            DocumentedRuleInterface::class
        );

        $message = sprintf('Found %d documented rule classes', count($documentedRuleClasses));
        $this->symfonyStyle->note($message);

        $classes = array_map(
            static fn (RuleClassWithFilePath $ruleClassWithFilePath): string => $ruleClassWithFilePath->getClass(),
            $documentedRuleClasses
        );

        $this->symfonyStyle->listing($classes);

        $this->symfonyStyle->note('Resolving rule definitions');

        $documentedRules = $this->ruleDefinitionsResolver->resolveFromClassNames($documentedRuleClasses);

        $this->symfonyStyle->note('Printing rule definitions');
        $markdownLines = $this->ruleDefinitionsPrinter->print($documentedRules);

        $fileContent = '';
        foreach ($markdownLines as $markdownLine) {
            $fileContent .= trim($markdownLine) . PHP_EOL . PHP_EOL;
        }

        return rtrim($fileContent) . PHP_EOL;
    }
}
