<?php

declare(strict_types=1);

namespace a9f\FractorXml\Printer;

use a9f\FractorXml\Printer\Source\SourceAttribute;
use a9f\FractorXml\Printer\Source\SourceDocument;
use a9f\FractorXml\Printer\Source\SourceElement;
use a9f\FractorXml\Printer\Source\SourceMarkup;
use a9f\FractorXml\Printer\Source\SourceText;

/**
 * Splits a well-formed XML file into the pieces the author wrote, keeping
 * everything libxml forgets: attribute layout and order, quotes, entities and
 * the whitespace between elements.
 */
final class SourceParser
{
    private const DECLARATION = '/\G<\?xml\s.*?\?>/s';

    private const START_TAG = '/\G<([^\s\/>]+)((?:\s+[^\s=\/>]+\s*=\s*(?:"[^"]*"|\'[^\']*\'))*)(\s*)(\/?)>/';

    private const ATTRIBUTE = '/(\s+)([^\s=\/>]+)\s*=\s*(?:"([^"]*)"|\'([^\']*)\')/';

    private const END_TAG = '/\G<\/([^\s>]+)\s*>/';

    private const BYTE_ORDER_MARK = "\xEF\xBB\xBF";

    private const TAB_WIDTH = 4;

    public function parse(string $xml): SourceDocument
    {
        $hasByteOrderMark = str_starts_with($xml, self::BYTE_ORDER_MARK);
        $contentStart = $hasByteOrderMark ? strlen(self::BYTE_ORDER_MARK) : 0;
        $position = $contentStart;

        $declaration = null;
        if (preg_match(self::DECLARATION, $xml, $match, 0, $position) === 1) {
            $declaration = $match[0];
            $position += strlen($declaration);
        }

        $document = new SourceDocument($hasByteOrderMark, $declaration, $this->lineBreakOf($xml));
        /** @var list<SourceElement> $openElements */
        $openElements = [];
        $lineStart = $this->lineStartAfter($xml, $contentStart, $position, $contentStart);
        $length = strlen($xml);

        while ($position < $length) {
            $parent = $openElements === [] ? $document : $openElements[count($openElements) - 1];

            if ($xml[$position] !== '<') {
                $end = strpos($xml, '<', $position);
                $end = $end === false ? $length : $end;
                $parent->addChild(new SourceText(substr($xml, $position, $end - $position)));
            } elseif ($this->startsWith($xml, '<!--', $position)) {
                $end = $this->endOf($xml, '-->', $position);
                $parent->addChild(new SourceMarkup(SourceMarkup::COMMENT, substr($xml, $position, $end - $position)));
            } elseif ($this->startsWith($xml, '<![CDATA[', $position)) {
                $end = $this->endOf($xml, ']]>', $position);
                $parent->addChild(SourceMarkup::cdata(substr($xml, $position, $end - $position)));
            } elseif ($this->startsWith($xml, '<!DOCTYPE', $position)) {
                $end = $this->endOfDocumentType($xml, $position);
                $parent->addChild(
                    new SourceMarkup(SourceMarkup::DOCUMENT_TYPE, substr($xml, $position, $end - $position))
                );
            } elseif ($this->startsWith($xml, '<?', $position)) {
                $end = $this->endOf($xml, '?>', $position);
                $parent->addChild(
                    new SourceMarkup(SourceMarkup::PROCESSING_INSTRUCTION, substr($xml, $position, $end - $position))
                );
            } elseif ($this->startsWith($xml, '</', $position)) {
                if (preg_match(self::END_TAG, $xml, $match, 0, $position) !== 1) {
                    throw new SourceMismatchException(sprintf('Unreadable end tag at byte %d.', $position));
                }

                $element = array_pop($openElements);
                if ($element === null || $element->name !== $match[1]) {
                    throw new SourceMismatchException(sprintf('Unexpected end tag "%s".', $match[1]));
                }

                $end = $position + strlen($match[0]);
            } else {
                $element = $this->parseStartTag($xml, $position, $lineStart);
                $parent->addChild($element);
                if (! $element->isSelfClosing) {
                    $openElements[] = $element;
                }

                $end = $position + strlen($element->rawStartTag);
            }

            $lineStart = $this->lineStartAfter($xml, $position, $end, $lineStart);
            $position = $end;
        }

        if ($openElements !== []) {
            throw new SourceMismatchException('The file ends inside an element.');
        }

        return $document;
    }

    private function parseStartTag(string $xml, int $position, int $lineStart): SourceElement
    {
        if (preg_match(self::START_TAG, $xml, $match, 0, $position) !== 1) {
            throw new SourceMismatchException(sprintf('Unreadable start tag at byte %d.', $position));
        }

        preg_match_all(self::ATTRIBUTE, $match[2], $attributeMatches, PREG_SET_ORDER);
        $attributes = [];
        foreach ($attributeMatches as $attributeMatch) {
            $leadingWhitespace = $attributeMatch[1];
            $singleQuotedValue = $attributeMatch[4] ?? null;
            $attributes[] = new SourceAttribute(
                $attributeMatch[2],
                substr($attributeMatch[0], strlen($leadingWhitespace)),
                $singleQuotedValue ?? $attributeMatch[3] ?? '',
                $singleQuotedValue === null ? '"' : "'",
                $leadingWhitespace
            );
        }

        $closingWhitespace = $match[3];
        $lastLineBreak = strrpos($closingWhitespace, "\n");
        $line = substr($xml, $lineStart, $position - $lineStart);

        return new SourceElement(
            $match[1],
            $attributes,
            $closingWhitespace,
            $match[4] === '/',
            $match[0],
            $this->width(substr($line, 0, strspn($line, " \t"))),
            $lastLineBreak === false ? 0 : $this->width(substr($closingWhitespace, $lastLineBreak + 1))
        );
    }

    /**
     * The line break of the first line, so a stray "\r\n" in a text does not change the whole file.
     */
    private function lineBreakOf(string $xml): string
    {
        $firstLineBreak = strpos($xml, "\n");

        return $firstLineBreak !== false && $firstLineBreak > 0 && $xml[$firstLineBreak - 1] === "\r" ? "\r\n" : "\n";
    }

    private function startsWith(string $xml, string $prefix, int $position): bool
    {
        return substr_compare($xml, $prefix, $position, strlen($prefix)) === 0;
    }

    private function endOf(string $xml, string $terminator, int $position): int
    {
        $found = strpos($xml, $terminator, $position);
        if ($found === false) {
            throw new SourceMismatchException(sprintf('Missing "%s" after byte %d.', $terminator, $position));
        }

        return $found + strlen($terminator);
    }

    private function endOfDocumentType(string $xml, int $position): int
    {
        $depth = 0;
        $quote = null;
        $length = strlen($xml);
        for ($index = $position; $index < $length; ++$index) {
            $character = $xml[$index];
            if ($quote !== null) {
                $quote = $character === $quote ? null : $quote;
            } elseif ($character === '"' || $character === "'") {
                $quote = $character;
            } elseif ($character === '[') {
                ++$depth;
            } elseif ($character === ']') {
                --$depth;
            } elseif ($character === '>' && $depth === 0) {
                return $index + 1;
            }
        }

        throw new SourceMismatchException('Unterminated document type declaration.');
    }

    private function lineStartAfter(string $xml, int $start, int $end, int $lineStart): int
    {
        $lastLineBreak = strrpos(substr($xml, $start, $end - $start), "\n");

        return $lastLineBreak === false ? $lineStart : $start + $lastLineBreak + 1;
    }

    private function width(string $indentation): int
    {
        return strlen($indentation) + substr_count($indentation, "\t") * (self::TAB_WIDTH - 1);
    }
}
