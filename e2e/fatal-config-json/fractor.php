<?php

declare(strict_types=1);

// a config that throws an exception with a previous exception, a Windows line break, a slash and invalid UTF-8
$rootCause = new \InvalidArgumentException("Root \xff cause in config/fractor.php");

throw new \RuntimeException("Outer message\r\nsecond line", 0, $rootCause);
