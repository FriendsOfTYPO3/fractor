<?php

declare(strict_types=1);

namespace a9f\FractorTypoScript\Tests\Fixtures;

use a9f\Fractor\RuleDoc\RuleDefinition;
use a9f\FractorTypoScript\AbstractTypoScriptFractor;
use Helmich\TypoScriptParser\Parser\AST\ObjectPath;
use Helmich\TypoScriptParser\Parser\AST\Operator\Assignment;
use Helmich\TypoScriptParser\Parser\AST\Operator\Copy;
use Helmich\TypoScriptParser\Parser\AST\Scalar;
use Helmich\TypoScriptParser\Parser\AST\Statement;
use Symfony\Component\DependencyInjection\Exception\BadMethodCallException;

final class ReturnMultipleStatementsRule extends AbstractTypoScriptFractor
{
    /**
     * @return null|list<Statement>
     */
    public function refactor(Statement $statement): null|array
    {
        if (! $statement instanceof Copy) {
            return null;
        }

        if ($statement->target->relativeName !== 'styles.content.get') {
            return null;
        }

        return [
            new Assignment($statement->object, new Scalar('CONTENT'), $statement->sourceLine),
            new Assignment(
                new ObjectPath(
                    $statement->object->absoluteName . '.value',
                    $statement->object->absoluteName . '.value'
                ),
                new Scalar('tt_content'),
                $statement->sourceLine + 1
            ),
            new Assignment(
                new ObjectPath(
                    $statement->object->absoluteName . '.where',
                    $statement->object->absoluteName . '.where'
                ),
                new Scalar('{#colPos} = 0'),
                $statement->sourceLine + 2
            ),
        ];
    }

    public function getRuleDefinition(): RuleDefinition
    {
        throw new BadMethodCallException('Not implemented yet');
    }
}
