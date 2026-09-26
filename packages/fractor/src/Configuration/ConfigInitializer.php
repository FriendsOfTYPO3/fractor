<?php

declare(strict_types=1);

namespace a9f\Fractor\Configuration;

use a9f\Fractor\Application\Contract\FractorRule;
use a9f\Fractor\Console\TerminalDetector;
use a9f\Fractor\FileSystem\InitFilePathsResolver;
use Nette\Utils\FileSystem;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\Filesystem\Path;

final class ConfigInitializer
{
    /**
     * @var FractorRule[]
     */
    private array $fractors;

    /**
     * @param RewindableGenerator<FractorRule>|FractorRule[] $fractors
     */
    public function __construct(
        iterable $fractors,
        private readonly InitFilePathsResolver $initFilePathsResolver,
        private readonly SymfonyStyle $symfonyStyle,
        private readonly TerminalDetector $terminalDetector,
        private readonly ?string $mainConfigFile,
    ) {
        if ($fractors instanceof RewindableGenerator) {
            $this->fractors = iterator_to_array($fractors->getIterator());
        } else {
            /** @var FractorRule[] $fractors */
            $this->fractors = $fractors;
        }
    }

    public function createConfig(string $projectDirectory, bool $isDryRun = false): void
    {
        $commonFractorConfigPath = $projectDirectory . '/fractor.php';

        // a config was loaded, it just registers no rules: point to it instead of creating another one - checked first,
        // so that a fractor.php in the working directory is not named when --config points elsewhere
        if ($this->mainConfigFile !== null && file_exists($this->mainConfigFile)) {
            $this->symfonyStyle->warning(sprintf(
                'Register rules or sets in your "%s" config',
                $this->resolveDisplayPath($this->mainConfigFile, $projectDirectory)
            ));
            return;
        }

        if (file_exists($commonFractorConfigPath)) {
            $this->symfonyStyle->warning('Register rules or sets in your "fractor.php" config');
            return;
        }

        // non-interactive terminal, e.g. piped input, CI or an agent: never prompt and never write a config on the
        // default answer - Symfony does not check for a terminal, and without input it takes the default answer "yes"
        if (! $this->terminalDetector->isInputTty()) {
            $this->symfonyStyle->warning('No "fractor.php" config found. Create one, or pass "--config <path>".');
            return;
        }

        // in a terminal, a dry run writes no files either - and its short option -n also makes Symfony answer every question with the default
        if ($isDryRun) {
            $this->symfonyStyle->warning(
                'No "fractor.php" config found. Create one, or run without --dry-run to generate it.'
            );
            return;
        }

        $response = $this->symfonyStyle->ask('No "fractor.php" config found. Should we generate it for you?', 'yes');
        // be tolerant about input
        if (! in_array($response, ['yes', 'YES', 'y', 'Y'], true)) {
            // okay, nothing we can do
            return;
        }

        $configContents = FileSystem::read(__DIR__ . '/../../templates/fractor.php.dist');
        $configContents = $this->replacePathsContents($configContents, $projectDirectory);

        FileSystem::write($commonFractorConfigPath, $configContents, null);
        $this->symfonyStyle->success('The config is added now. Re-run command to make Fractor do the work!');
    }

    public function areSomeFractorsLoaded(): bool
    {
        return $this->fractors !== [];
    }

    /**
     * The path relative to the project directory, so that two files named fractor.php are told apart
     */
    private function resolveDisplayPath(string $filePath, string $projectDirectory): string
    {
        // the working directory is empty when getcwd() fails, e.g. after it was deleted
        if (Path::isAbsolute($projectDirectory) && Path::isBasePath($projectDirectory, $filePath)) {
            return Path::makeRelative($filePath, $projectDirectory);
        }

        return $filePath;
    }

    private function replacePathsContents(string $rectorPhpTemplateContents, string $projectDirectory): string
    {
        $projectPhpDirectories = $this->initFilePathsResolver->resolve($projectDirectory);

        // fallback to default 'src' in case of empty one
        if ($projectPhpDirectories === []) {
            $projectPhpDirectories[] = 'src';
        }

        $projectPhpDirectoriesContents = '';
        foreach ($projectPhpDirectories as $projectPhpDirectory) {
            $projectPhpDirectoriesContents .= "        __DIR__ . '/" . $projectPhpDirectory . "'," . PHP_EOL;
        }

        $projectPhpDirectoriesContents = rtrim($projectPhpDirectoriesContents);

        return str_replace('__PATHS__', $projectPhpDirectoriesContents, $rectorPhpTemplateContents);
    }
}
