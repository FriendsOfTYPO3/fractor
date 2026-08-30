<?php

declare(strict_types=1);

namespace a9f\Typo3Fractor\TYPO3v13\TypoScript;

use a9f\Fractor\Contract\FilesystemInterface;
use a9f\Fractor\Contract\LocalFilesystemInterface;
use a9f\FractorTypoScript\AbstractTypoScriptFractor;
use a9f\Typo3Fractor\Utility\ExtensionManagementUtility;
use Helmich\TypoScriptParser\Parser\AST\Builder;
use Helmich\TypoScriptParser\Parser\AST\ConditionalStatement;
use Helmich\TypoScriptParser\Parser\AST\ConditionalStatementTerminator;
use Helmich\TypoScriptParser\Parser\AST\DirectoryIncludeStatement;
use Helmich\TypoScriptParser\Parser\AST\FileIncludeStatement;
use Helmich\TypoScriptParser\Parser\AST\Statement;
use League\Flysystem\FileAttributes;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/13.4/Deprecation-105171-INCLUDE_TYPOSCRIPTTypoScriptSyntax.html
 * @see \a9f\Typo3Fractor\Tests\TYPO3v13\TypoScript\MigrateIncludeTypoScriptSyntaxFractor\MigrateIncludeTypoScriptSyntaxFractorTest
 */
final class MigrateIncludeTypoScriptSyntaxFractor extends AbstractTypoScriptFractor
{
    public function __construct(
        private readonly Builder $builder,
        private readonly FilesystemInterface $filesystem,
        private readonly LocalFilesystemInterface $localFilesystem,
        private readonly ExtensionManagementUtility $extensionManagementUtility
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Migrate INCLUDE_TYPOSCRIPT TypoScript syntax to @import',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
<INCLUDE_TYPOSCRIPT: source="FILE:EXT:my_extension/Configuration/TypoScript/myMenu.typoscript">
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
@import 'EXT:my_extension/Configuration/TypoScript/myMenu.typoscript'
CODE_SAMPLE
                ),
                new CodeSample(
                    <<<'CODE_SAMPLE'
<INCLUDE_TYPOSCRIPT: source="DIR:EXT:my_extension/Configuration/TypoScript/" extensions="typoscript">
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
@import 'EXT:my_extension/Configuration/TypoScript/*.typoscript'
CODE_SAMPLE
                ),
                new CodeSample(
                    <<<'CODE_SAMPLE'
<INCLUDE_TYPOSCRIPT: source="DIR:EXT:my_extension/Configuration/TypoScript/" extensions="typoscript,ts">
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
@import 'EXT:my_extension/Configuration/TypoScript/*.typoscript'
CODE_SAMPLE
                ),
                new CodeSample(
                    <<<'CODE_SAMPLE'
<INCLUDE_TYPOSCRIPT: source="DIR:EXT:my_extension/Configuration/TypoScript/" extensions="setup.typoscript">
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
@import 'EXT:my_extension/Configuration/TypoScript/*.setup.typoscript'
CODE_SAMPLE
                ),
                new CodeSample(
                    <<<'CODE_SAMPLE'
<INCLUDE_TYPOSCRIPT: source="DIR:EXT:my_extension/Configuration/TypoScript/" extensions="constants.typoscript">
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
@import 'EXT:my_extension/Configuration/TypoScript/*.constants.typoscript'
CODE_SAMPLE
                ),
                new CodeSample(
                    <<<'CODE_SAMPLE'
<INCLUDE_TYPOSCRIPT: source="DIR:EXT:my_extension/Configuration/TypoScript/" extensions="tsconfig">
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
@import 'EXT:my_extension/Configuration/TypoScript/*.tsconfig'
CODE_SAMPLE
                ),
                new CodeSample(
                    <<<'CODE_SAMPLE'
<INCLUDE_TYPOSCRIPT: source="FILE:EXT:my_extension/Configuration/TypoScript/user.typoscript" condition="[frontend.user.isLoggedIn]">
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
[frontend.user.isLoggedIn]
    @import 'EXT:my_extension/Configuration/TypoScript/user.typoscript'
[end]
CODE_SAMPLE
                ),
            ]
        );
    }

    public function refactor(Statement $statement): null|Statement
    {
        if ($this->shouldSkip($statement)) {
            return null;
        }

        if ($statement instanceof FileIncludeStatement) {
            $statement->newSyntax = true;

            if ($statement->condition !== null) {
                return new ConditionalStatement(
                    $statement->condition,
                    [$statement],
                    [],
                    $statement->sourceLine,
                    ConditionalStatementTerminator::End
                );
            }

            return $statement;
        }

        /** @var DirectoryIncludeStatement $statement */
        $extensions = 'typoscript';

        $directory = rtrim($statement->directory, '/') . '/';
        if ($statement->extensions !== null) {
            $extensionParts = array_map(trim(...), explode(',', $statement->extensions));

            if (in_array('ts', $extensionParts, true)) {
                $extensionRootPath = $this->extensionManagementUtility->resolveExtensionPath(
                    $this->file->getFilePath()
                );
                $extensionKey = basename($extensionRootPath);
                $typoScriptIncludePath = $extensionRootPath . str_replace('EXT:' . $extensionKey, '', $directory);

                $listing = $this->localFilesystem->listContents($typoScriptIncludePath, false);
                foreach ($listing as $item) {
                    if (! $item instanceof FileAttributes) {
                        continue;
                    }

                    $source = $item->path();

                    // Rename all *.ts files to *.typoscript
                    if (str_ends_with($source, '.ts')) {
                        $destination = str_replace('.ts', '.typoscript', $source);
                        if ($this->filesystem->fileExists($source)) {
                            $this->filesystem->move($source, $destination);
                        }
                    }
                }

                $extensions = 'typoscript';
            } else {
                $extensions = $statement->extensions;
            }

            $importStatement = $directory . '*.' . $extensions;
        } else {
            $importStatement = $directory . '*';
        }
        return $this->builder->includeFile($importStatement, true, $statement->condition, $statement->sourceLine);
    }

    private function shouldSkip(Statement $statement): bool
    {
        if (! $statement instanceof FileIncludeStatement && ! $statement instanceof DirectoryIncludeStatement) {
            return true;
        }

        if ($statement instanceof FileIncludeStatement && $statement->newSyntax) {
            return true;
        }

        return false;
    }
}
