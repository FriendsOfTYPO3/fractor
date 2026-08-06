<?php

declare(strict_types=1);

namespace a9f\Fractor\Console\Application;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputDefinition;

final class FractorApplication extends Application
{
    public const NAME = 'Fractor';

    public function __construct()
    {
        $fractorVersion = \Composer\InstalledVersions::getPrettyVersion('a9f/fractor') ?? 'dev';
        parent::__construct(self::NAME, $fractorVersion);
        // run this command, if no command name is provided
        $this->setDefaultCommand('process');
    }

    protected function getDefaultInputDefinition(): InputDefinition
    {
        $defaultInputDefinition = parent::getDefaultInputDefinition();
        $this->removeUnusedOptions($defaultInputDefinition);
        return $defaultInputDefinition;
    }

    private function removeUnusedOptions(InputDefinition $inputDefinition): void
    {
        $options = $inputDefinition->getOptions();
        unset($options['quiet'], $options['no-interaction']);
        $inputDefinition->setOptions($options);
    }
}
