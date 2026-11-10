<?php

declare(strict_types=1);

namespace a9f\FractorExtensionInstaller;

use Composer\Installer\InstallationManager;
use Composer\Repository\InstalledRepositoryInterface;
use Composer\Util\Filesystem;

class PackagesFileGenerator
{
    public const FILE_TEMPLATE = <<<PHP
<?php

namespace a9f\FractorExtensionInstaller\Generated;

class InstalledPackages {
    public const PACKAGES = %s;

    private function __construct() {}
}
PHP;

    public function __construct(
        private readonly InstalledRepositoryInterface $repository,
        private readonly InstallationManager $installationManager,
        private readonly string $fileToGenerate
    ) {
    }

    public function generate(): void
    {
        $installedPackages = [];
        foreach ($this->repository->getCanonicalPackages() as $package) {
            if ($package->getType() !== 'fractor-extension') {
                continue;
            }

            $path = $this->installationManager->getInstallPath($package);

            if ($path === null) {
                continue;
            }

            $installedPackages[$package->getName()] = [
                'path' => $path,
            ];
        }

        self::write($installedPackages, $this->fileToGenerate);
    }

    /**
     * @param array<string, array{'path': string}> $installedPackages
     */
    public static function write(array $installedPackages, string $fileToGenerate): void
    {
        // Paths relative to the generated file, so it stays valid when vendor/ is used at another
        // absolute path than during "composer install", e.g. on the host vs. inside a container
        $filesystem = new Filesystem();
        $installedPackagesCode = "[\n";
        foreach ($installedPackages as $name => $package) {
            $installedPackagesCode .= sprintf(
                "        %s => ['path' => %s],\n",
                var_export($name, true),
                $filesystem->findShortestPathCode($fileToGenerate, $package['path'], false, true)
            );
        }
        $installedPackagesCode .= '    ]';

        file_put_contents($fileToGenerate, sprintf(self::FILE_TEMPLATE, $installedPackagesCode));
    }
}
