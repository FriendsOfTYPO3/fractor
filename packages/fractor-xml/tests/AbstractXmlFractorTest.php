<?php

declare(strict_types=1);

namespace a9f\FractorXml\Tests;

use a9f\Fractor\Application\ValueObject\File;
use a9f\Fractor\RuleDoc\CodeSample\CodeSample;
use a9f\Fractor\RuleDoc\RuleDefinition;
use a9f\FractorXml\AbstractXmlFractor;
use a9f\FractorXml\DomDocumentIterator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class AbstractXmlFractorTest extends TestCase
{
    #[Test]
    public function refactorAddsAppliedRulesToFile(): void
    {
        $nodeReplacingRule = new class() extends AbstractXmlFractor {
            public function canHandle(\DOMNode $node): bool
            {
                return $node->nodeName === 'Child';
            }

            public function refactor(\DOMNode $node): \DOMElement
            {
                if ($node->ownerDocument === null) {
                    throw new \RuntimeException('Node does not have an ownerDocument, cannot create element');
                }

                return $node->ownerDocument->createElement('NewChild');
            }

            public function getRuleDefinition(): RuleDefinition
            {
                return new RuleDefinition('', [new CodeSample('', '')]);
            }
        };

        $document = new \DOMDocument();
        $document->loadXML(<<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<Root><Child><GrandChild /></Child></Root>
XML);
        $subject = new DomDocumentIterator([$nodeReplacingRule]);
        $file = new File('does-not-matter.xml', '');
        $subject->traverseDocument($file, $document);

        self::assertCount(1, $file->getAppliedRules());
        self::assertStringStartsWith(
            'a9f\\FractorXml\\AbstractXmlFractor@anonymous',
            $file->getAppliedRules()[0]
                ->getFractorClass()
        );
    }
}
