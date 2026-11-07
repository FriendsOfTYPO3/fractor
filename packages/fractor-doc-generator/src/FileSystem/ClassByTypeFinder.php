<?php

declare(strict_types=1);

namespace a9f\FractorDocGenerator\FileSystem;

use a9f\FractorDocGenerator\ValueObject\RuleClassWithFilePath;
use Nette\Loaders\RobotLoader;
use ReflectionClass;
use Symfony\Component\Filesystem\Filesystem;

final class ClassByTypeFinder
{
    /**
     * @param string[] $directories
     * @return RuleClassWithFilePath[]
     */
    public function findByType(string $workingDirectory, array $directories, string $type): array
    {
        $robotLoader = new RobotLoader();
        $robotLoader->setTempDirectory(sys_get_temp_dir() . '/robot_loader_temp');
        $robotLoader->addDirectory(...$directories);
        $robotLoader->ignoreDirs[] = '*tests*';
        $robotLoader->ignoreDirs[] = '*Fixture*';
        $robotLoader->ignoreDirs[] = '*templates*';

        $robotLoader->rebuild();

        $desiredClasses = [];
        foreach ($robotLoader->getIndexedClasses() as $class => $filePath) {
            if (! is_a($class, $type, true)) {
                continue;
            }

            $reflectionClass = new ReflectionClass($class);
            if ($reflectionClass->isAbstract()) {
                continue;
            }

            $relativeFilePath = $this->relativeFromDirectory($filePath, $workingDirectory);
            $desiredClasses[] = new RuleClassWithFilePath($class, $relativeFilePath);
        }

        usort(
            $desiredClasses,
            static fn (RuleClassWithFilePath $left, RuleClassWithFilePath $right): int => $left->getClass() <=> $right->getClass()
        );

        return $desiredClasses;
    }

    private function relativeFromDirectory(string $filePath, string $directory): string
    {
        $relativeFilePath = (new Filesystem())->makePathRelative(
            (string) realpath($filePath),
            (string) realpath($directory)
        );

        return rtrim($relativeFilePath, '/');
    }
}
