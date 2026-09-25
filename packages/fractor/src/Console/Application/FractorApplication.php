<?php

declare(strict_types=1);

namespace a9f\Fractor\Console\Application;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputDefinition;

final class FractorApplication extends Application
{
    public const NAME = 'Fractor';

    public const FRACTOR_CONSOLE_VERSION = '1.1.0';

    public function __construct()
    {
        parent::__construct(self::NAME, self::FRACTOR_CONSOLE_VERSION);
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
