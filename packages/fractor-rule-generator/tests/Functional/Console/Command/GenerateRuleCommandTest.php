<?php

declare(strict_types=1);

namespace a9f\FractorRuleGenerator\Tests\Functional\Console\Command;

use a9f\Fractor\FileSystem\FileInfoFactory;
use a9f\FractorRuleGenerator\Console\Command\GenerateRuleCommand;
use a9f\FractorRuleGenerator\Factory\TemplateFactory;
use a9f\FractorRuleGenerator\FileSystem\ConfigFilesystemWriter;
use a9f\FractorRuleGenerator\FileSystem\TemplateFileSystem;
use a9f\FractorRuleGenerator\Finder\TemplateFinder;
use a9f\FractorRuleGenerator\Generator\FileGenerator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

final class GenerateRuleCommandTest extends TestCase
{
    /**
     * @var array<int, string>
     */
    private array $testFilesToDelete = [];

    /**
     * @var array<int, string>
     */
    private array $testDirsToDelete = [];

    private CommandTester $commandTester;

    protected function setUp(): void
    {
        parent::setUp();

        $fileSystem = new Filesystem();
        $templateFactory = new TemplateFactory();
        $templateFileSystem = new TemplateFileSystem($fileSystem);

        $fileInfoFactory = new FileInfoFactory($fileSystem);
        $templateFinder = new TemplateFinder($fileInfoFactory);
        $fileGenerator = new FileGenerator($fileSystem, $templateFactory, $templateFileSystem);

        $outputStyle = new BufferedOutput();

        $configFilesystemWriter = new ConfigFilesystemWriter($fileSystem, $templateFactory);

        $createdCommand = new GenerateRuleCommand(
            $templateFinder,
            $fileGenerator,
            $outputStyle,
            $configFilesystemWriter,
            $fileInfoFactory
        );

        $application = new Application();
        $application->add($createdCommand);

        $foundCommand = $application->find('generate-rule');

        $this->commandTester = new CommandTester($foundCommand);
    }

    /**
     * Tear down for remove of the test files
     */
    protected function tearDown(): void
    {
        foreach ($this->testFilesToDelete as $absoluteFileName) {
            if (@is_file($absoluteFileName)) {
                unlink($absoluteFileName);
            }
        }
        foreach ($this->testDirsToDelete as $absoluteDirName) {
            if (@is_dir($absoluteDirName)) {
                self::rmdir($absoluteDirName, true);
            }
        }
        parent::tearDown();
    }

    public function testCreateRuleForFlexFormWithoutChangelog(): void
    {
        $this->commandTester->setInputs(['9999', 'x', 'MigrateFlexForm', 'Migrate FlexForm field', '0']);
        $this->commandTester->execute([
            'command' => 'generate-rule',
        ]);

        self::assertSame(0, $this->commandTester->getStatusCode());

        $basePathConfig = __DIR__ . '/../../../../../typo3-fractor/config';
        $basePathRules = __DIR__ . '/../../../../../typo3-fractor/rules';
        $basePathRuleTests = __DIR__ . '/../../../../../typo3-fractor/rules-tests';

        $this->testFilesToDelete[] = $basePathConfig . '/typo3-9999.php';
        self::assertFileExists($basePathConfig . '/typo3-9999.php');
        self::assertFileExists($basePathRules . '/TYPO3v9999/FlexForm/MigrateFlexFormFractor.php');
        self::assertFileExists($basePathRuleTests . '/TYPO3v9999/FlexForm/MigrateFlexFormFractor/config/fractor.php');
        self::assertFileExists(
            $basePathRuleTests . '/TYPO3v9999/FlexForm/MigrateFlexFormFractor/Fixtures/fixture.xml.fixture'
        );
        self::assertFileExists(
            $basePathRuleTests . '/TYPO3v9999/FlexForm/MigrateFlexFormFractor/MigrateFlexFormFractorTest.php.inc'
        );

        $fractorFileContent = file_get_contents($basePathRules . '/TYPO3v9999/FlexForm/MigrateFlexFormFractor.php');
        if ($fractorFileContent !== false) {
            self::assertStringContainsString('use a9f\Fractor\Contract\NoChangelogRequired;', $fractorFileContent);
            self::assertStringContainsString(
                'extends AbstractFlexformFractor implements NoChangelogRequired',
                $fractorFileContent
            );
        }

        $this->testDirsToDelete[] = $basePathRules . '/TYPO3v9999';
        $this->testDirsToDelete[] = $basePathRuleTests . '/TYPO3v9999';
    }

    public function testCreateRuleForFlexFormWithChangelog(): void
    {
        $this->commandTester->setInputs(
            [
                '9999',
                'https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/9999.6/Feature-123-Bla.html',
                'MigrateFlexForm',
                'Migrate FlexForm field',
                '0',
            ]
        );
        $this->commandTester->execute([
            'command' => 'generate-rule',
        ]);

        self::assertSame(0, $this->commandTester->getStatusCode());

        $basePathConfig = __DIR__ . '/../../../../../typo3-fractor/config';
        $basePathRules = __DIR__ . '/../../../../../typo3-fractor/rules';
        $basePathRuleTests = __DIR__ . '/../../../../../typo3-fractor/rules-tests';

        $this->testFilesToDelete[] = $basePathConfig . '/typo3-9999.php';
        self::assertFileExists($basePathConfig . '/typo3-9999.php');
        self::assertFileExists($basePathRules . '/TYPO3v9999/FlexForm/MigrateFlexFormFractor.php');
        self::assertFileExists($basePathRuleTests . '/TYPO3v9999/FlexForm/MigrateFlexFormFractor/config/fractor.php');
        self::assertFileExists(
            $basePathRuleTests . '/TYPO3v9999/FlexForm/MigrateFlexFormFractor/Fixtures/fixture.xml.fixture'
        );
        self::assertFileExists(
            $basePathRuleTests . '/TYPO3v9999/FlexForm/MigrateFlexFormFractor/MigrateFlexFormFractorTest.php.inc'
        );

        $fractorFileContent = file_get_contents($basePathRules . '/TYPO3v9999/FlexForm/MigrateFlexFormFractor.php');
        if ($fractorFileContent !== false) {
            self::assertStringContainsString(
                '@changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/9999.6/Feature-123-Bla.html',
                $fractorFileContent
            );
        }

        $this->testDirsToDelete[] = $basePathRules . '/TYPO3v9999';
        $this->testDirsToDelete[] = $basePathRuleTests . '/TYPO3v9999';
    }

    public function testCreateRuleForFluidWithoutChangelog(): void
    {
        $this->commandTester->setInputs(['9999', 'x', 'MigrateFluid', 'Migrate Fluid field', '1']);

        $this->commandTester->execute([
            'command' => 'generate-rule',
        ]);

        self::assertSame(0, $this->commandTester->getStatusCode());

        $basePathConfig = __DIR__ . '/../../../../../typo3-fractor/config';
        $basePathRules = __DIR__ . '/../../../../../typo3-fractor/rules';
        $basePathRuleTests = __DIR__ . '/../../../../../typo3-fractor/rules-tests';

        $this->testFilesToDelete[] = $basePathConfig . '/typo3-9999.php';
        self::assertFileExists($basePathConfig . '/typo3-9999.php');
        self::assertFileExists($basePathRules . '/TYPO3v9999/Fluid/MigrateFluidFractor.php');
        self::assertFileExists($basePathRuleTests . '/TYPO3v9999/Fluid/MigrateFluidFractor/config/fractor.php');
        self::assertFileExists(
            $basePathRuleTests . '/TYPO3v9999/Fluid/MigrateFluidFractor/Fixtures/fixture.html.fixture'
        );
        self::assertFileExists(
            $basePathRuleTests . '/TYPO3v9999/Fluid/MigrateFluidFractor/MigrateFluidFractorTest.php.inc'
        );

        $fractorFileContent = file_get_contents($basePathRules . '/TYPO3v9999/Fluid/MigrateFluidFractor.php');
        if ($fractorFileContent !== false) {
            self::assertStringContainsString('use a9f\Fractor\Contract\NoChangelogRequired;', $fractorFileContent);
            self::assertStringContainsString('implements FluidFractorRule, NoChangelogRequired', $fractorFileContent);
        }

        $this->testDirsToDelete[] = $basePathRules . '/TYPO3v9999';
        $this->testDirsToDelete[] = $basePathRuleTests . '/TYPO3v9999';
    }

    public function testCreateRuleForFluidWithChangelog(): void
    {
        $this->commandTester->setInputs(
            [
                '9999',
                'https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/9999.6/Feature-123-Bla.html',
                'MigrateFluid',
                'Migrate Fluid field',
                '1',
            ]
        );
        $this->commandTester->execute([
            'command' => 'generate-rule',
        ]);

        self::assertSame(0, $this->commandTester->getStatusCode());

        $basePathConfig = __DIR__ . '/../../../../../typo3-fractor/config';
        $basePathRules = __DIR__ . '/../../../../../typo3-fractor/rules';
        $basePathRuleTests = __DIR__ . '/../../../../../typo3-fractor/rules-tests';

        $this->testFilesToDelete[] = $basePathConfig . '/typo3-9999.php';
        self::assertFileExists($basePathConfig . '/typo3-9999.php');
        self::assertFileExists($basePathRules . '/TYPO3v9999/Fluid/MigrateFluidFractor.php');
        self::assertFileExists($basePathRuleTests . '/TYPO3v9999/Fluid/MigrateFluidFractor/config/fractor.php');
        self::assertFileExists(
            $basePathRuleTests . '/TYPO3v9999/Fluid/MigrateFluidFractor/Fixtures/fixture.html.fixture'
        );
        self::assertFileExists(
            $basePathRuleTests . '/TYPO3v9999/Fluid/MigrateFluidFractor/MigrateFluidFractorTest.php.inc'
        );

        $fractorFileContent = file_get_contents($basePathRules . '/TYPO3v9999/Fluid/MigrateFluidFractor.php');
        if ($fractorFileContent !== false) {
            self::assertStringContainsString(
                '@changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/9999.6/Feature-123-Bla.html',
                $fractorFileContent
            );
        }

        $this->testDirsToDelete[] = $basePathRules . '/TYPO3v9999';
        $this->testDirsToDelete[] = $basePathRuleTests . '/TYPO3v9999';
    }

    public function testCreateRuleForTypoScriptWithoutChangelog(): void
    {
        $this->commandTester->setInputs(['9999', 'x', 'MigrateTypoScript', 'Migrate TypoScript setting', '2']);
        $this->commandTester->execute([
            'command' => 'generate-rule',
        ]);

        self::assertSame(0, $this->commandTester->getStatusCode());

        $basePathConfig = __DIR__ . '/../../../../../typo3-fractor/config';
        $basePathRules = __DIR__ . '/../../../../../typo3-fractor/rules';
        $basePathRuleTests = __DIR__ . '/../../../../../typo3-fractor/rules-tests';

        $this->testFilesToDelete[] = $basePathConfig . '/typo3-9999.php';
        self::assertFileExists($basePathConfig . '/typo3-9999.php');
        self::assertFileExists($basePathRules . '/TYPO3v9999/TypoScript/MigrateTypoScriptFractor.php');
        self::assertFileExists(
            $basePathRuleTests . '/TYPO3v9999/TypoScript/MigrateTypoScriptFractor/config/fractor.php'
        );
        self::assertFileExists(
            $basePathRuleTests . '/TYPO3v9999/TypoScript/MigrateTypoScriptFractor/Fixtures/fixture.typoscript.fixture'
        );
        self::assertFileExists(
            $basePathRuleTests . '/TYPO3v9999/TypoScript/MigrateTypoScriptFractor/MigrateTypoScriptFractorTest.php.inc'
        );

        $fractorFileContent = file_get_contents($basePathRules . '/TYPO3v9999/TypoScript/MigrateTypoScriptFractor.php');
        if ($fractorFileContent !== false) {
            self::assertStringContainsString('use a9f\Fractor\Contract\NoChangelogRequired;', $fractorFileContent);
            self::assertStringContainsString(
                'extends AbstractTypoScriptFractor implements NoChangelogRequired',
                $fractorFileContent
            );
        }

        $this->testDirsToDelete[] = $basePathRules . '/TYPO3v9999';
        $this->testDirsToDelete[] = $basePathRuleTests . '/TYPO3v9999';
    }

    public function testCreateRuleForTypoScriptWithChangelog(): void
    {
        $this->commandTester->setInputs(
            [
                '9999',
                'https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/9999.6/Feature-123-Bla.html',
                'MigrateTypoScript',
                'Migrate TypoScript setting',
                '2',
            ]
        );
        $this->commandTester->execute([
            'command' => 'generate-rule',
        ]);

        self::assertSame(0, $this->commandTester->getStatusCode());

        $basePathConfig = __DIR__ . '/../../../../../typo3-fractor/config';
        $basePathRules = __DIR__ . '/../../../../../typo3-fractor/rules';
        $basePathRuleTests = __DIR__ . '/../../../../../typo3-fractor/rules-tests';

        $this->testFilesToDelete[] = $basePathConfig . '/typo3-9999.php';
        self::assertFileExists($basePathConfig . '/typo3-9999.php');
        self::assertFileExists($basePathRules . '/TYPO3v9999/TypoScript/MigrateTypoScriptFractor.php');
        self::assertFileExists(
            $basePathRuleTests . '/TYPO3v9999/TypoScript/MigrateTypoScriptFractor/config/fractor.php'
        );
        self::assertFileExists(
            $basePathRuleTests . '/TYPO3v9999/TypoScript/MigrateTypoScriptFractor/Fixtures/fixture.typoscript.fixture'
        );
        self::assertFileExists(
            $basePathRuleTests . '/TYPO3v9999/TypoScript/MigrateTypoScriptFractor/MigrateTypoScriptFractorTest.php.inc'
        );

        $fractorFileContent = file_get_contents($basePathRules . '/TYPO3v9999/TypoScript/MigrateTypoScriptFractor.php');
        if ($fractorFileContent !== false) {
            self::assertStringContainsString(
                '@changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/9999.6/Feature-123-Bla.html',
                $fractorFileContent
            );
        }

        $this->testDirsToDelete[] = $basePathRules . '/TYPO3v9999';
        $this->testDirsToDelete[] = $basePathRuleTests . '/TYPO3v9999';
    }

    public function testCreateRuleForYamlWithoutChangelog(): void
    {
        $this->commandTester->setInputs(['9999', 'x', 'MigrateYaml', 'Migrate Yaml setting', '3']);
        $this->commandTester->execute([
            'command' => 'generate-rule',
        ]);

        self::assertSame(0, $this->commandTester->getStatusCode());

        $basePathConfig = __DIR__ . '/../../../../../typo3-fractor/config';
        $basePathRules = __DIR__ . '/../../../../../typo3-fractor/rules';
        $basePathRuleTests = __DIR__ . '/../../../../../typo3-fractor/rules-tests';

        $this->testFilesToDelete[] = $basePathConfig . '/typo3-9999.php';
        self::assertFileExists($basePathConfig . '/typo3-9999.php');
        self::assertFileExists($basePathRules . '/TYPO3v9999/Yaml/MigrateYamlFractor.php');
        self::assertFileExists($basePathRuleTests . '/TYPO3v9999/Yaml/MigrateYamlFractor/config/fractor.php');
        self::assertFileExists(
            $basePathRuleTests . '/TYPO3v9999/Yaml/MigrateYamlFractor/Fixtures/fixture.yaml.fixture'
        );
        self::assertFileExists(
            $basePathRuleTests . '/TYPO3v9999/Yaml/MigrateYamlFractor/MigrateYamlFractorTest.php.inc'
        );

        $fractorFileContent = file_get_contents($basePathRules . '/TYPO3v9999/Yaml/MigrateYamlFractor.php');
        if ($fractorFileContent !== false) {
            self::assertStringContainsString('use a9f\Fractor\Contract\NoChangelogRequired;', $fractorFileContent);
            self::assertStringContainsString('implements YamlFractorRule, NoChangelogRequired', $fractorFileContent);
        }

        $this->testDirsToDelete[] = $basePathRules . '/TYPO3v9999';
        $this->testDirsToDelete[] = $basePathRuleTests . '/TYPO3v9999';
    }

    public function testCreateRuleForYamlWithChangelog(): void
    {
        $this->commandTester->setInputs(
            [
                '9999',
                'https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/9999.6/Feature-123-Bla.html',
                'MigrateYaml',
                'Migrate Yaml setting',
                '3',
            ]
        );
        $this->commandTester->execute([
            'command' => 'generate-rule',
        ]);

        self::assertSame(0, $this->commandTester->getStatusCode());

        $basePathConfig = __DIR__ . '/../../../../../typo3-fractor/config';
        $basePathRules = __DIR__ . '/../../../../../typo3-fractor/rules';
        $basePathRuleTests = __DIR__ . '/../../../../../typo3-fractor/rules-tests';

        $this->testFilesToDelete[] = $basePathConfig . '/typo3-9999.php';
        self::assertFileExists($basePathConfig . '/typo3-9999.php');
        self::assertFileExists($basePathRules . '/TYPO3v9999/Yaml/MigrateYamlFractor.php');
        self::assertFileExists($basePathRuleTests . '/TYPO3v9999/Yaml/MigrateYamlFractor/config/fractor.php');
        self::assertFileExists(
            $basePathRuleTests . '/TYPO3v9999/Yaml/MigrateYamlFractor/Fixtures/fixture.yaml.fixture'
        );
        self::assertFileExists(
            $basePathRuleTests . '/TYPO3v9999/Yaml/MigrateYamlFractor/MigrateYamlFractorTest.php.inc'
        );

        $fractorFileContent = file_get_contents($basePathRules . '/TYPO3v9999/Yaml/MigrateYamlFractor.php');
        if ($fractorFileContent !== false) {
            self::assertStringContainsString(
                '@changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/9999.6/Feature-123-Bla.html',
                $fractorFileContent
            );
        }

        $this->testDirsToDelete[] = $basePathRules . '/TYPO3v9999';
        $this->testDirsToDelete[] = $basePathRuleTests . '/TYPO3v9999';
    }

    public function testCreateRuleForComposerWithoutChangelog(): void
    {
        $this->commandTester->setInputs(['9999', 'x', 'MigrateComposer', 'Migrate Composer setting', '4']);
        $this->commandTester->execute([
            'command' => 'generate-rule',
        ]);

        self::assertSame(0, $this->commandTester->getStatusCode());

        $basePathConfig = __DIR__ . '/../../../../../typo3-fractor/config';
        $basePathRules = __DIR__ . '/../../../../../typo3-fractor/rules';
        $basePathRuleTests = __DIR__ . '/../../../../../typo3-fractor/rules-tests';

        $this->testFilesToDelete[] = $basePathConfig . '/typo3-9999.php';
        self::assertFileExists($basePathConfig . '/typo3-9999.php');
        self::assertFileExists($basePathRules . '/TYPO3v9999/Composer/MigrateComposerFractor.php');
        self::assertFileExists($basePathRuleTests . '/TYPO3v9999/Composer/MigrateComposerFractor/config/fractor.php');
        self::assertFileExists(
            $basePathRuleTests . '/TYPO3v9999/Composer/MigrateComposerFractor/Fixtures/fixture.json.fixture'
        );
        self::assertFileExists(
            $basePathRuleTests . '/TYPO3v9999/Composer/MigrateComposerFractor/MigrateComposerFractorTest.php.inc'
        );

        $fractorFileContent = file_get_contents($basePathRules . '/TYPO3v9999/Composer/MigrateComposerFractor.php');
        if ($fractorFileContent !== false) {
            self::assertStringContainsString('use a9f\Fractor\Contract\NoChangelogRequired;', $fractorFileContent);
            self::assertStringContainsString(
                'implements ComposerJsonFractorRule, NoChangelogRequired',
                $fractorFileContent
            );
        }

        $this->testDirsToDelete[] = $basePathRules . '/TYPO3v9999';
        $this->testDirsToDelete[] = $basePathRuleTests . '/TYPO3v9999';
    }

    public function testCreateRuleForComposerWithChangelog(): void
    {
        $this->commandTester->setInputs(
            [
                '9999',
                'https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/9999.6/Feature-123-Bla.html',
                'MigrateComposer',
                'Migrate Composer setting',
                '4',
            ]
        );
        $this->commandTester->execute([
            'command' => 'generate-rule',
        ]);

        self::assertSame(0, $this->commandTester->getStatusCode());

        $basePathConfig = __DIR__ . '/../../../../../typo3-fractor/config';
        $basePathRules = __DIR__ . '/../../../../../typo3-fractor/rules';
        $basePathRuleTests = __DIR__ . '/../../../../../typo3-fractor/rules-tests';

        $this->testFilesToDelete[] = $basePathConfig . '/typo3-9999.php';
        self::assertFileExists($basePathConfig . '/typo3-9999.php');
        self::assertFileExists($basePathRules . '/TYPO3v9999/Composer/MigrateComposerFractor.php');
        self::assertFileExists($basePathRuleTests . '/TYPO3v9999/Composer/MigrateComposerFractor/config/fractor.php');
        self::assertFileExists(
            $basePathRuleTests . '/TYPO3v9999/Composer/MigrateComposerFractor/Fixtures/fixture.json.fixture'
        );
        self::assertFileExists(
            $basePathRuleTests . '/TYPO3v9999/Composer/MigrateComposerFractor/MigrateComposerFractorTest.php.inc'
        );

        $fractorFileContent = file_get_contents($basePathRules . '/TYPO3v9999/Composer/MigrateComposerFractor.php');
        if ($fractorFileContent !== false) {
            self::assertStringContainsString(
                '@changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/9999.6/Feature-123-Bla.html',
                $fractorFileContent
            );
        }

        $this->testDirsToDelete[] = $basePathRules . '/TYPO3v9999';
        $this->testDirsToDelete[] = $basePathRuleTests . '/TYPO3v9999';
    }

    public function testCreateRuleForHtaccessWithoutChangelog(): void
    {
        $this->commandTester->setInputs(['9999', 'x', 'MigrateHtaccess', 'Migrate Htaccess field', '5']);

        $this->commandTester->execute([
            'command' => 'generate-rule',
        ]);

        self::assertSame(0, $this->commandTester->getStatusCode());

        $basePathConfig = __DIR__ . '/../../../../../typo3-fractor/config';
        $basePathRules = __DIR__ . '/../../../../../typo3-fractor/rules';
        $basePathRuleTests = __DIR__ . '/../../../../../typo3-fractor/rules-tests';

        $this->testFilesToDelete[] = $basePathConfig . '/typo3-9999.php';
        self::assertFileExists($basePathConfig . '/typo3-9999.php');
        self::assertFileExists($basePathRules . '/TYPO3v9999/Htaccess/MigrateHtaccessFractor.php');
        self::assertFileExists($basePathRuleTests . '/TYPO3v9999/Htaccess/MigrateHtaccessFractor/config/fractor.php');
        self::assertFileExists(
            $basePathRuleTests . '/TYPO3v9999/Htaccess/MigrateHtaccessFractor/Fixtures/fixture.htaccess.fixture'
        );
        self::assertFileExists(
            $basePathRuleTests . '/TYPO3v9999/Htaccess/MigrateHtaccessFractor/MigrateHtaccessFractorTest.php.inc'
        );

        $fractorFileContent = file_get_contents($basePathRules . '/TYPO3v9999/Htaccess/MigrateHtaccessFractor.php');
        if ($fractorFileContent !== false) {
            self::assertStringContainsString('use a9f\Fractor\Contract\NoChangelogRequired;', $fractorFileContent);
            self::assertStringContainsString(
                'implements HtaccessFractorRule, NoChangelogRequired',
                $fractorFileContent
            );
        }

        $this->testDirsToDelete[] = $basePathRules . '/TYPO3v9999';
        $this->testDirsToDelete[] = $basePathRuleTests . '/TYPO3v9999';
    }

    public function testCreateRuleForHtaccessWithChangelog(): void
    {
        $this->commandTester->setInputs(
            [
                '9999',
                'https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/9999.6/Feature-123-Bla.html',
                'MigrateHtaccess',
                'Migrate Htaccess field',
                '5',
            ]
        );
        $this->commandTester->execute([
            'command' => 'generate-rule',
        ]);

        self::assertSame(0, $this->commandTester->getStatusCode());

        $basePathConfig = __DIR__ . '/../../../../../typo3-fractor/config';
        $basePathRules = __DIR__ . '/../../../../../typo3-fractor/rules';
        $basePathRuleTests = __DIR__ . '/../../../../../typo3-fractor/rules-tests';

        $this->testFilesToDelete[] = $basePathConfig . '/typo3-9999.php';
        self::assertFileExists($basePathConfig . '/typo3-9999.php');
        self::assertFileExists($basePathRules . '/TYPO3v9999/Htaccess/MigrateHtaccessFractor.php');
        self::assertFileExists($basePathRuleTests . '/TYPO3v9999/Htaccess/MigrateHtaccessFractor/config/fractor.php');
        self::assertFileExists(
            $basePathRuleTests . '/TYPO3v9999/Htaccess/MigrateHtaccessFractor/Fixtures/fixture.htaccess.fixture'
        );
        self::assertFileExists(
            $basePathRuleTests . '/TYPO3v9999/Htaccess/MigrateHtaccessFractor/MigrateHtaccessFractorTest.php.inc'
        );

        $fractorFileContent = file_get_contents($basePathRules . '/TYPO3v9999/Htaccess/MigrateHtaccessFractor.php');
        if ($fractorFileContent !== false) {
            self::assertStringContainsString(
                '@changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/9999.6/Feature-123-Bla.html',
                $fractorFileContent
            );
        }

        $this->testDirsToDelete[] = $basePathRules . '/TYPO3v9999';
        $this->testDirsToDelete[] = $basePathRuleTests . '/TYPO3v9999';
    }

    /**
     * Wrapper function for rmdir, allowing recursive deletion of folders and files
     *
     * @param string $path Absolute path to folder, see PHP rmdir() function. Removes trailing slash internally.
     * @param bool $removeNonEmpty Allow deletion of non-empty directories
     * @return bool TRUE if operation was successful
     * @source TYPO3: \TYPO3\CMS\Core\Utility\GeneralUtility::rmdir
     */
    private static function rmdir(string $path, bool $removeNonEmpty = false): bool
    {
        $OK = false;
        // Remove trailing slash
        $path = preg_replace('|/$|', '', $path) ?? '';
        $isWindows = DIRECTORY_SEPARATOR === '\\';
        if (file_exists($path)) {
            $OK = true;
            if (! is_link($path) && is_dir($path)) {
                if ($removeNonEmpty && ($handle = @opendir($path))) {
                    $entries = [];

                    while (false !== ($file = readdir($handle))) {
                        if ($file === '.') {
                            continue;
                        }
                        if ($file === '..') {
                            continue;
                        }
                        $entries[] = $path . '/' . $file;
                    }

                    closedir($handle);

                    foreach ($entries as $entry) {
                        if (! static::rmdir($entry, true)) {
                            $OK = false;
                        }
                    }
                }
                if ($OK) {
                    $OK = @rmdir($path);
                }
            } elseif (is_link($path) && is_dir($path) && $isWindows) {
                $OK = @rmdir($path);
            } else {
                // If $path is a file, simply remove it
                $OK = @unlink($path);
            }
            clearstatcache();
        } elseif (is_link($path)) {
            $OK = @unlink($path);
            if (! $OK && $isWindows) {
                // Try to delete dead folder links on Windows systems
                $OK = @rmdir($path);
            }
            clearstatcache();
        }
        return $OK;
    }
}
