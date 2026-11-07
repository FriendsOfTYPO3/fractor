<?php

declare(strict_types=1);

namespace a9f\FractorXml\Tests\Fixtures;

use a9f\Fractor\RuleDoc\RuleDefinition;
use a9f\Typo3Fractor\AbstractFlexformFractor;
use a9f\Typo3Fractor\Helper\FlexFormHelperTrait;
use Symfony\Component\DependencyInjection\Exception\BadMethodCallException;

final class DummyXmlFractorRule extends AbstractFlexformFractor
{
    use FlexFormHelperTrait;

    public function canHandle(\DOMNode $node): bool
    {
        return parent::canHandle($node) && $node->nodeName === 'config';
    }

    public function refactor(\DOMNode $node): \DOMNode|null
    {
        if (! $node instanceof \DOMElement) {
            return null;
        }
        $this->removeChildElementFromDomElementByKey($node, 'max');
        return $node;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        throw new BadMethodCallException('Not implemented yet');
    }
}
