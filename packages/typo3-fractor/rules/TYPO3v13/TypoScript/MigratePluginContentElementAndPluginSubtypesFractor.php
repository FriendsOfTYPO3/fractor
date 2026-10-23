<?php

declare(strict_types=1);

namespace a9f\Typo3Fractor\TYPO3v13\TypoScript;

use a9f\FractorTypoScript\AbstractTypoScriptFractor;
use Helmich\TypoScriptParser\Parser\AST\Builder;
use Helmich\TypoScriptParser\Parser\AST\NestedAssignment;
use Helmich\TypoScriptParser\Parser\AST\Operator\Assignment;
use Helmich\TypoScriptParser\Parser\AST\Operator\Copy;
use Helmich\TypoScriptParser\Parser\AST\Operator\Reference;
use Helmich\TypoScriptParser\Parser\AST\Statement;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/13.4/Deprecation-105076-PluginContentElementAndPluginSubTypes.html
 * @see \a9f\Typo3Fractor\Tests\TYPO3v13\TypoScript\MigratePluginContentElementAndPluginSubtypesFractor\MigratePluginContentElementAndPluginSubtypesFractorTest
 */
final class MigratePluginContentElementAndPluginSubtypesFractor extends AbstractTypoScriptFractor
{
    public function __construct(
        private readonly Builder $builder
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Migrate plugin content element and plugin subtypes (list_type)', [new CodeSample(
            <<<'CODE_SAMPLE'
tt_content.list.20.examples_pi1 = USER
tt_content.list.20.examples_pi1 {
    userFunc = MyVendor\Examples\Controller\ExampleController->example
}

tt_content.list.20.examples_pi1 < plugin.tx_examples_pi1
CODE_SAMPLE
            ,
            <<<'CODE_SAMPLE'
tt_content.examples_pi1 =< lib.contentElement
tt_content.examples_pi1 {
    20 = USER
    20 {
        userFunc = MyVendor\Examples\Controller\ExampleController->example
    }
    templateName = Generic
}

tt_content.examples_pi1.20 < plugin.tx_examples_pi1
CODE_SAMPLE
        ), new CodeSample(
            <<<'CODE_SAMPLE'
tt_content.list.20.examples_pi1 < plugin.tx_examples_pi1
CODE_SAMPLE
            ,
            <<<'CODE_SAMPLE'
tt_content.examples_pi1.20 < plugin.tx_examples_pi1
CODE_SAMPLE
        )]);
    }

    public function refactor(Statement $statement): null|Statement|int
    {
        if ($this->shouldSkip($statement)) {
            return null;
        }

        if ($statement instanceof NestedAssignment) {
            $rootLine = ['tt_content', 'list', '20', '#'];
            /** @var NestedAssignment|null $pluginStatement */
            $pluginStatement = $this->findPluginStatement($statement, $rootLine);
            if ($pluginStatement === null) {
                return null;
            }

            $pluginStatement->object->absoluteName = str_replace(
                'list.20.',
                '',
                $pluginStatement->object->absoluteName
            );
            $pluginStatement->object->relativeName = str_replace(
                'list.20.',
                '',
                $pluginStatement->object->relativeName
            );

            if ($pluginStatement->object->absoluteName === $pluginStatement->object->relativeName) {
                return $pluginStatement;
            }
            $statement->statements[0] = $pluginStatement;
        } elseif ($statement instanceof Assignment) {
            $statement->object->absoluteName = str_replace('list.20.', '', $statement->object->absoluteName);
            $statement->object->relativeName = str_replace('list.20.', '', $statement->object->relativeName);

            $objectPathLeft = $this->builder->path(
                str_replace('list.20.', '', $statement->object->absoluteName),
                str_replace('list.20.', '', $statement->object->relativeName)
            );
            $objectPathRight = $this->builder->path('tt_content.lib.contentElement', 'lib.contentElement');

            $reference = new Reference($objectPathLeft, $objectPathRight, $statement->sourceLine);

        } elseif ($statement instanceof Copy || $statement instanceof Reference) {
            $statement->object->absoluteName = str_replace('list.20.', '', $statement->object->absoluteName);
            $statement->object->relativeName = str_replace('list.20.', '', $statement->object->relativeName);

            $statement->target->absoluteName = str_replace('list.20.', '', $statement->target->absoluteName);
            $statement->target->relativeName = str_replace('list.20.', '', $statement->target->relativeName);
        }

        return $statement;
    }

    private function shouldSkip(Statement $statement): bool
    {
        if (! $statement instanceof NestedAssignment
            && ! $statement instanceof Assignment
            && ! $statement instanceof Copy
            && ! $statement instanceof Reference
        ) {
            return true;
        }

        if (($statement instanceof Copy || $statement instanceof Reference)) {
            return ! str_starts_with($statement->target->relativeName, 'tt_content')
                && ! str_starts_with($statement->object->absoluteName, 'tt_content');
        }

        if (! str_starts_with($statement->object->absoluteName, 'tt_content')) {
            return true;
        }

        return false;
    }

    /**
     * @param array<int, string> $rootLine
     */
    private function findPluginStatement(Statement $statement, array $rootLine): ?Statement
    {
        if (! $statement instanceof NestedAssignment) {
            return null;
        }

        if (! isset($rootLine[0])) {
            return $statement;
        }

        $objectPath = $statement->object->relativeName;
        $parts = explode('.', $objectPath);
        foreach ($parts as $part) {
            $firstRootLineItem = $rootLine[0];
            if ($firstRootLineItem === $part || $firstRootLineItem === '#') {
                array_shift($rootLine);
            }
        }

        if ($rootLine === []) {
            // we found it!
            return $statement;
        }

        return $this->findPluginStatement($statement->statements[0], $rootLine);
    }
}
