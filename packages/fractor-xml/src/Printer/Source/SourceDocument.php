<?php

declare(strict_types=1);

namespace a9f\FractorXml\Printer\Source;

final class SourceDocument
{
    /**
     * @var list<SourceNode>
     */
    public array $children = [];

    /**
     * @param string $lineBreak the line break the file uses, "\n" or "\r\n"
     */
    public function __construct(
        public readonly bool $hasByteOrderMark,
        public readonly ?string $declaration,
        public readonly string $lineBreak
    ) {
    }

    public function addChild(SourceNode $child): void
    {
        $child->parent = $this;
        $child->index = count($this->children);
        $this->children[] = $child;
    }
}
