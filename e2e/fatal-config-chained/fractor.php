<?php

declare(strict_types=1);

// a config that throws an exception chain with Windows and old Mac line breaks, and invalid UTF-8
$rootCause = new \InvalidArgumentException("Root \xff cause");
$middleCause = new \LogicException('Middle cause', 0, $rootCause);

throw new \RuntimeException("Outer message\r\nsecond line\rthird line", 0, $middleCause);
