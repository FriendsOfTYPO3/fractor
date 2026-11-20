<?php

declare(strict_types=1);

namespace a9f\FractorXml\Printer;

use a9f\FractorXml\Printer\Source\SourceAttribute;
use a9f\FractorXml\Printer\Source\SourceElement;
use a9f\FractorXml\Printer\Source\SourceMarkup;
use a9f\FractorXml\Printer\Source\SourceText;

/**
 * Prints one document, see XmlPrinter for the rules.
 *
 * @internal
 */
final readonly class DocumentPrinter
{
    private const BYTE_ORDER_MARK = "\xEF\xBB\xBF";

    private \DOMXPath $xpath;

    /**
     * @var \SplObjectStorage<\DOMElement, array<string, string>>
     */
    private \SplObjectStorage $namespacesInScope;

    private bool $isOriginalDocument;

    private string $lineBreak;

    public function __construct(
        private \DOMDocument $document,
        private string $indent,
        private ?SourceMap $sourceMap
    ) {
        $this->xpath = new \DOMXPath($document);
        $this->namespacesInScope = new \SplObjectStorage();
        $this->isOriginalDocument = $sourceMap?->isOriginalDocument($document) === true;
        $this->lineBreak = $sourceMap instanceof SourceMap && $this->isOriginalDocument
            ? $sourceMap->source->lineBreak
            : "\n";
    }

    public function print(): string
    {
        $output = $this->sourceMap instanceof SourceMap
            && $this->isOriginalDocument
            && $this->sourceMap->source->hasByteOrderMark ? self::BYTE_ORDER_MARK : '';

        $declaration = $this->declaration();
        $content = $this->printChildrenOnOwnLines($this->document, 0, $declaration !== null);
        if ($declaration === null) {
            $content = substr($content, strlen($this->lineBreak));
        }

        return $output . $declaration . $content . $this->lineBreak;
    }

    private function declaration(): ?string
    {
        if ($this->sourceMap instanceof SourceMap && $this->sourceMap->hasOriginalDeclaration($this->document)) {
            return $this->sourceMap->source->declaration;
        }

        $declaration = '<?xml version="' . ($this->document->xmlVersion ?? '1.0') . '"';
        if ((string) $this->document->xmlEncoding !== '') {
            $declaration .= ' encoding="' . $this->document->xmlEncoding . '"';
        }

        if ($this->document->xmlStandalone) {
            $declaration .= ' standalone="yes"';
        }

        return $declaration . '?>';
    }

    /**
     * Element content: every child on its own line, single blank lines between children kept.
     */
    private function printChildrenOnOwnLines(\DOMNode $parent, int $depth, bool $followsDeclaration = false): string
    {
        $output = '';
        $isFirst = ! $followsDeclaration;
        foreach ($parent->childNodes as $child) {
            if ($this->isWhitespaceText($child)) {
                continue;
            }

            $blankLine = ! $isFirst && $this->followsBlankLine($child) ? $this->lineBreak : '';
            $output .= $this->lineBreak . $blankLine . $this->indentation($depth) . $this->printNode($child, $depth);
            $isFirst = false;
        }

        return $output;
    }

    private function followsBlankLine(\DOMNode $node): bool
    {
        $source = $this->sourceMap?->sourceOf($node);
        $previous = $source?->parent?->children[$source->index - 1] ?? null;

        return $previous instanceof SourceText && $previous->isWhitespace && $previous->countLineBreaks() >= 2;
    }

    /**
     * Text content: printed as written.
     *
     * libxml drops some whitespace between elements in text content; it is taken from the file
     * as long as the nodes around it are still next to each other.
     */
    private function printChildrenAsWritten(\DOMElement $element, ?SourceElement $source): string
    {
        $hasText = $this->hasNonWhitespaceText($element);
        $isAsWritten = $source instanceof SourceElement && $this->hasWrittenStructure($element, $source);
        // Whitespace between elements only is formatting: once the elements change, it would not survive the next run
        $skipsWhitespace = ! $hasText && ! $isAsWritten;
        $restoresWhitespace = $source instanceof SourceElement
            && ($hasText ? $source->hasTextContent() : $isAsWritten);
        $output = '';
        $previous = -1;
        foreach ($element->childNodes as $child) {
            if ($skipsWhitespace && $this->isWhitespaceText($child)) {
                continue;
            }

            $childSource = $this->sourceMap?->sourceOf($child);
            $index = $restoresWhitespace && $childSource !== null && $childSource->parent === $source
                ? $childSource->index
                : null;
            if ($source instanceof SourceElement && $index !== null && $previous !== null) {
                $output .= $this->droppedWhitespace($source, $previous + 1, $index);
            }

            $output .= $child instanceof \DOMElement
                ? $this->printElement($child, 0, true)
                : $this->printNode($child, 0);
            $previous = $index === null || $childSource === null ? null : $index + $childSource->span - 1;
        }

        if ($source instanceof SourceElement && $restoresWhitespace && $previous !== null) {
            $output .= $this->droppedWhitespace($source, $previous + 1, count($source->children));
        }

        return $output;
    }

    private function hasNonWhitespaceText(\DOMElement $element): bool
    {
        foreach ($element->childNodes as $child) {
            if ($child instanceof \DOMCdataSection || $child instanceof \DOMEntityReference) {
                return true;
            }

            if ($child instanceof \DOMText && ! $this->isWhitespaceText($child)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Whether the element still has exactly the children it was written with, apart from whitespace.
     */
    private function hasWrittenStructure(\DOMElement $element, SourceElement $source): bool
    {
        $written = [];
        foreach ($source->children as $child) {
            if (! $child instanceof SourceText || ! $child->isWhitespace) {
                $written[] = $child->index;
            }
        }

        $current = [];
        foreach ($element->childNodes as $child) {
            if ($this->isWhitespaceText($child)) {
                continue;
            }

            $childSource = $this->sourceMap?->sourceOf($child);
            if ($childSource === null || $childSource->parent !== $source) {
                return false;
            }

            for ($index = $childSource->index; $index < $childSource->index + $childSource->span; ++$index) {
                $piece = $source->children[$index];
                if (! $piece instanceof SourceText || ! $piece->isWhitespace) {
                    $current[] = $index;
                }
            }
        }

        return $current === $written;
    }

    /**
     * Returns the whitespace libxml dropped between two pieces, or nothing if anything else was between them.
     */
    private function droppedWhitespace(SourceElement $source, int $from, int $to): string
    {
        $whitespace = '';
        for ($index = $from; $index < $to; ++$index) {
            $sibling = $source->children[$index];
            if (! $sibling instanceof SourceText || ! $sibling->isWhitespace || $sibling->isMapped) {
                return '';
            }

            $whitespace .= $sibling->raw;
        }

        return $whitespace;
    }

    private function printNode(\DOMNode $node, int $depth): string
    {
        return match (true) {
            $node instanceof \DOMElement => $this->printElement($node, $depth, false),
            $node instanceof \DOMCdataSection => $this->rawIfUnchanged(
                $node,
                '<![CDATA[' . str_replace(']]>', ']]]]><![CDATA[>', $node->data) . ']]>'
            ),
            $node instanceof \DOMText => $this->rawIfUnchanged($node, XmlEscaper::escapeText($node->data)),
            $node instanceof \DOMComment => $this->rawIfUnchanged($node, '<!--' . $node->data . '-->'),
            $node instanceof \DOMProcessingInstruction => $this->rawIfUnchanged(
                $node,
                '<?' . $node->target . ($node->data === '' ? '' : ' ' . $node->data) . '?>'
            ),
            $node instanceof \DOMEntityReference => '&' . $node->nodeName . ';',
            default => $this->printDocumentType($node),
        };
    }

    private function printElement(\DOMElement $element, int $depth, bool $isInline): string
    {
        $source = $this->sourceMap?->sourceOf($element);
        $source = $source instanceof SourceElement ? $source : null;
        $name = $element->nodeName;
        $startTag = '<' . $name . $this->printAttributes($element, $source, $depth, $isInline);

        if (! $element->hasChildNodes()) {
            if ($source instanceof SourceElement && ! $source->isSelfClosing && $source->children === []) {
                return $startTag . $this->closeStartTag('>', $source, $depth, $isInline) . '</' . $name . '>';
            }

            return $startTag . $this->closeStartTag('/>', $source, $depth, $isInline);
        }

        $startTag .= $this->closeStartTag('>', $source, $depth, $isInline);
        if (! $isInline && $this->hasOnlyLineBreakingWhitespace($element)) {
            return $startTag . $this->lineBreak . $this->indentation($depth) . '</' . $name . '>';
        }

        if ($isInline || $this->hasTextContent($element)) {
            return $startTag . $this->printChildrenAsWritten($element, $source) . '</' . $name . '>';
        }

        return $startTag
            . $this->printChildrenOnOwnLines($element, $depth + 1)
            . $this->lineBreak . $this->indentation($depth) . '</' . $name . '>';
    }

    /**
     * Attributes keep their place, line and spelling; new ones go to the end, on their
     * own line if the last attribute has its own line.
     */
    private function printAttributes(\DOMElement $element, ?SourceElement $source, int $depth, bool $isInline): string
    {
        $namespaces = $this->namespacesInScope($element);
        $newDeclarations = $this->declaredNamespaces($element, $namespaces);
        $attributes = [];
        foreach ($element->attributes ?? [] as $attribute) {
            if ($attribute instanceof \DOMAttr) {
                $attributes[$attribute->nodeName] = $attribute->value;
            }
        }

        $output = '';
        $lastIsOnOwnLine = ! $isInline && ($source?->attributes[0] ?? null)?->isOnNewLine() === true;
        if ($source instanceof SourceElement) {
            foreach ($source->attributes as $sourceAttribute) {
                $name = $sourceAttribute->name;
                if ($sourceAttribute->isNamespaceDeclaration()) {
                    $value = $this->ownNamespaceDeclaration($element, $sourceAttribute);
                    unset($newDeclarations[$name]);
                } else {
                    $value = $attributes[$name] ?? null;
                    unset($attributes[$name]);
                }

                if ($value === null) {
                    continue;
                }

                $output .= $isInline
                    ? $sourceAttribute->leadingWhitespace
                    : $this->separator($sourceAttribute->isOnNewLine(), $depth);
                $output .= ($source->loadedAttributes[$name] ?? null) === $value
                    ? $sourceAttribute->raw
                    : XmlEscaper::attribute($name, $value, $sourceAttribute->quote);
                $lastIsOnOwnLine = ! $isInline && $sourceAttribute->isOnNewLine();
            }
        }

        foreach ($newDeclarations + $attributes as $name => $value) {
            $output .= $this->separator($lastIsOnOwnLine, $depth) . XmlEscaper::attribute($name, $value);
        }

        return $output;
    }

    private function separator(bool $onNewLine, int $depth): string
    {
        return $onNewLine ? $this->lineBreak . $this->indentation($depth + 1) : ' ';
    }

    /**
     * ">" or "/>" on its own line stays there, on the level of the element or of its attributes.
     */
    private function closeStartTag(string $bracket, ?SourceElement $source, int $depth, bool $isInline): string
    {
        if (! $source instanceof SourceElement) {
            return $bracket;
        }

        $keepsBracket = ($bracket === '/>') === $source->isSelfClosing;
        if ($isInline) {
            return ($keepsBracket ? $source->closingWhitespace : '') . $bracket;
        }

        if ($source->hasClosingBracketOnOwnLine()) {
            $level = $source->closingIndentWidth > $source->indentWidth ? $depth + 1 : $depth;
            return $this->lineBreak . $this->indentation($level) . $bracket;
        }

        if ($keepsBracket && $bracket === '/>' && $source->closingWhitespace !== '') {
            return ' />';
        }

        return $bracket;
    }

    /**
     * @return array<string, string> namespace declaration name, e.g. "xmlns:xsi", to URI
     */
    private function namespacesInScope(\DOMElement $element): array
    {
        if ($this->namespacesInScope->contains($element)) {
            return $this->namespacesInScope[$element];
        }

        $namespaces = [];
        $nodes = $this->xpath->query('namespace::*', $element);
        foreach ($nodes === false ? [] : $nodes as $node) {
            if ($node->nodeName !== 'xmlns:xml') {
                $namespaces[$node->nodeName] = (string) $node->nodeValue;
            }
        }

        $this->namespacesInScope->attach($element, $namespaces);

        return $namespaces;
    }

    /**
     * @param array<string, string> $namespaces
     * @return array<string, string>
     */
    private function declaredNamespaces(\DOMElement $element, array $namespaces): array
    {
        $parent = $element->parentNode;
        $inherited = $parent instanceof \DOMElement ? $this->namespacesInScope($parent) : [];

        $declared = [];
        foreach ($namespaces as $name => $uri) {
            if (($inherited[$name] ?? null) !== $uri) {
                $declared[$name] = $uri;
            }
        }

        if (isset($inherited['xmlns']) && ! isset($namespaces['xmlns'])) {
            $declared['xmlns'] = '';
        }

        return $declared;
    }

    /**
     * Whitespace only counts as content when there is nothing else, e.g. <label> </label>.
     */
    private function hasTextContent(\DOMElement $element): bool
    {
        $hasMarkup = false;
        foreach ($element->childNodes as $child) {
            if ($child instanceof \DOMCdataSection || $child instanceof \DOMEntityReference) {
                return true;
            }

            if ($child instanceof \DOMText) {
                if (! $this->isWhitespaceText($child)) {
                    return true;
                }

                continue;
            }

            $hasMarkup = true;
        }

        return ! $hasMarkup;
    }

    /**
     * Whitespace with a line break is the only content, e.g. an empty <body> written over two lines.
     */
    private function hasOnlyLineBreakingWhitespace(\DOMElement $element): bool
    {
        $hasLineBreak = false;
        foreach ($element->childNodes as $child) {
            if (! $this->isWhitespaceText($child) || ! $child instanceof \DOMText) {
                return false;
            }

            $hasLineBreak = $hasLineBreak || str_contains($child->data, "\n");
        }

        return $hasLineBreak;
    }

    /**
     * A declaration the element itself still makes; the xml prefix is never stored by libxml.
     */
    private function ownNamespaceDeclaration(\DOMElement $element, SourceAttribute $sourceAttribute): ?string
    {
        if ($sourceAttribute->name === 'xmlns:xml') {
            return XmlEscaper::decodeAttribute($sourceAttribute->rawValue);
        }

        return $element->hasAttribute($sourceAttribute->name) ? $element->getAttribute($sourceAttribute->name) : null;
    }

    /**
     * A node still holding the value it was loaded with is printed as written, anything else
     * in the fixed form with the line break of the file.
     */
    private function rawIfUnchanged(\DOMCharacterData|\DOMProcessingInstruction $node, string $printed): string
    {
        $source = $this->sourceMap?->sourceOf($node);
        if ($source !== null && $source->loadedValue === $node->data) {
            return $source instanceof SourceText || $source instanceof SourceMarkup ? $source->raw : $printed;
        }

        return str_replace("\n", $this->lineBreak, $printed);
    }

    private function printDocumentType(\DOMNode $node): string
    {
        $source = $this->sourceMap?->sourceOf($node);
        if ($source instanceof SourceMarkup) {
            return $source->raw;
        }

        return (string) $this->document->saveXML($node);
    }

    /**
     * Whitespace between elements is formatting, unless it is written as a character reference.
     */
    private function isWhitespaceText(\DOMNode $node): bool
    {
        if (! $node instanceof \DOMText || $node instanceof \DOMCdataSection || ! XmlEscaper::isWhitespace(
            $node->data
        )) {
            return false;
        }

        $source = $this->sourceMap?->sourceOf($node);

        return ! $source instanceof SourceText || $source->isPlainWhitespace;
    }

    private function indentation(int $depth): string
    {
        return str_repeat($this->indent, $depth);
    }
}
