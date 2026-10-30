<?php

declare(strict_types=1);

use a9f\Fractor\Bootstrap\FractorConfigsResolver;
use a9f\Fractor\ChangesReporting\Output\JsonOutputFormatter;
use a9f\Fractor\Configuration\Option;
use a9f\Fractor\Console\Application\FractorApplication;
use a9f\Fractor\Console\Style\SymfonyStyleFactory;
use a9f\Fractor\DependencyInjection\FractorContainerFactory;
use a9f\Fractor\Util\Reflection\PrivatesAccessor;
use Nette\Utils\Json;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\OutputInterface;

$autoloadFile = (static function (): ?string {
    $candidates = [
        // first try the vendor folder, where fractor is installed to
        __DIR__ . '/../../../autoload.php',
        __DIR__ . '/../vendor/autoload.php',
        // fallback to the project's vendor folder
        getcwd() . '/vendor/autoload.php',
    ];
    foreach ($candidates as $candidate) {
        if (file_exists($candidate)) {
            return $candidate;
        }
    }
    return null;
})();
if ($autoloadFile === null) {
    echo 'Could not find autoload.php file';
    exit(1);
}
include $autoloadFile;

$fractorConfigsResolver = new FractorConfigsResolver();

try {
    $configFile = $fractorConfigsResolver->provide();

    $containerContainerBuilder = new FractorContainerFactory();
    $container = $containerContainerBuilder->createDependencyInjectionContainer($configFile);
} catch (\Throwable $throwable) {
    // collect the message of the exception and of every previous exception
    $errors = [];
    $locations = [];
    do {
        // replace invalid UTF-8 with U+FFFD: it cannot be encoded as JSON, and older symfony/console versions print an empty block
        $errors[] = (string) json_decode(
            json_encode($throwable->getMessage(), JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR),
            flags: JSON_THROW_ON_ERROR
        );
        $locations[] = sprintf(
            '%s in %s line %s',
            get_debug_type($throwable),
            basename($throwable->getFile()) ?: 'n/a',
            $throwable->getLine() ?: 'n/a'
        );
    } while ($throwable = $throwable->getPrevious());

    // for json output
    $argvInput = new ArgvInput();
    $outputFormat = $argvInput->getParameterOption('--' . Option::OUTPUT_FORMAT);

    // report fatal error in json format
    if ($outputFormat === JsonOutputFormatter::NAME) {
        echo Json::encode([
            'fatal_errors' => $errors,
        ]);
    } else {
        // report fatal errors in console format, on stderr, so that redirecting stdout does not hide them
        $symfonyStyleFactory = new SymfonyStyleFactory(new PrivatesAccessor());
        $symfonyStyle = $symfonyStyleFactory->create();
        // -q and SHELL_VERBOSITY=-1 must not hide the only output of a failed run
        if ($symfonyStyle->getVerbosity() < OutputInterface::VERBOSITY_NORMAL) {
            $symfonyStyle->setVerbosity(OutputInterface::VERBOSITY_NORMAL);
        }

        $errorStyle = $symfonyStyle->getErrorStyle();
        foreach ($errors as $index => $error) {
            $message = (string) preg_replace('/\r\n?/', "\n", $error);
            // -v and higher add where the exception was thrown
            if ($errorStyle->isVerbose()) {
                $message .= "\n" . $locations[$index];
            }

            $errorStyle->error($message);
        }
    }

    exit(Command::FAILURE);
}

/** @var FractorApplication $application */
$application = $container->get(FractorApplication::class);
exit($application->run());
