<?php

declare(strict_types=1);

namespace a9f\FractorXliff\Tests\Fixtures;

use a9f\Fractor\RuleDoc\RuleDefinition;
use a9f\FractorXliff\Contract\XliffFractorRule;
use a9f\FractorXliff\ValueObject\XliffDocument;
use Symfony\Component\DependencyInjection\Exception\BadMethodCallException;

final class DummyXliffFractorRule implements XliffFractorRule
{
    public function refactor(XliffDocument $xliffDocument): ?XliffDocument
    {
        $changed = false;
        $sourceElements = $xliffDocument->document->getElementsByTagName('source');

        foreach ($sourceElements as $sourceElement) {
            if ($sourceElement->textContent === 'Hello') {
                $sourceElement->textContent = 'Hello World';
                $changed = true;
            }
        }

        return $changed ? $xliffDocument : null;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        throw new BadMethodCallException('Not implemented yet');
    }
}
