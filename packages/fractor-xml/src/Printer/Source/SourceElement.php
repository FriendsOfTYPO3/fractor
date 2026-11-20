<?php

declare(strict_types=1);

namespace a9f\FractorXml\Printer\Source;

final class SourceElement extends SourceNode
{
    /**
     * @var list<SourceNode>
     */
    public array $children = [];

    /**
     * Attribute and namespace declaration values libxml reported when the file was loaded.
     *
     * @var array<string, string>
     */
    public array $loadedAttributes = [];

    /**
     * @param list<SourceAttribute> $attributes
     * @param string $closingWhitespace whitespace between the last attribute and ">" or "/>"
     * @param int $indentWidth width of the indentation in front of the start tag
     * @param int $closingIndentWidth width of the indentation in front of ">" or "/>" on its own line
     */
    public function __construct(
        public readonly string $name,
        public readonly array $attributes,
        public readonly string $closingWhitespace,
        public readonly bool $isSelfClosing,
        public readonly string $rawStartTag,
        public readonly int $indentWidth,
        public readonly int $closingIndentWidth
    ) {
    }

    public function addChild(SourceNode $child): void
    {
        $child->parent = $this;
        $child->index = count($this->children);
        $this->children[] = $child;
    }

    public function hasTextContent(): bool
    {
        foreach ($this->children as $child) {
            if (($child instanceof SourceText && ! $child->isPlainWhitespace)
                || ($child instanceof SourceMarkup && $child->kind === SourceMarkup::CDATA)) {
                return true;
            }
        }

        return false;
    }

    public function hasClosingBracketOnOwnLine(): bool
    {
        return str_contains($this->closingWhitespace, "\n");
    }
}
