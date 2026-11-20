<?php

declare(strict_types=1);

namespace a9f\FractorXml\Printer\Source;

use a9f\FractorXml\Printer\XmlEscaper;

final class SourceText extends SourceNode
{
    public readonly bool $isWhitespace;

    /**
     * Whitespace written as such, not as character references like &#32;.
     */
    public readonly bool $isPlainWhitespace;

    public function __construct(
        public readonly string $raw
    ) {
        $this->isWhitespace = XmlEscaper::isWhitespace(XmlEscaper::decodeText($raw));
        $this->isPlainWhitespace = XmlEscaper::isWhitespace($raw);
    }

    public function countLineBreaks(): int
    {
        return substr_count($this->raw, "\n");
    }
}
