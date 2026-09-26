<?php

declare(strict_types=1);

// a config that throws an exception chain, run with -v to show where each exception was created
$previous = new \LogicException('Inner cause');

throw new \Symfony\Component\Console\Exception\RuntimeException('Outer message', 0, $previous);
