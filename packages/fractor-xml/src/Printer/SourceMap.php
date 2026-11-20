<?php

declare(strict_types=1);

namespace a9f\FractorXml\Printer;

use a9f\FractorXml\Printer\Source\SourceDocument;
use a9f\FractorXml\Printer\Source\SourceElement;
use a9f\FractorXml\Printer\Source\SourceMarkup;
use a9f\FractorXml\Printer\Source\SourceNode;
use a9f\FractorXml\Printer\Source\SourceText;

/**
 * Links every DOM node of a freshly loaded document to the piece of the file it was read from.
 *
 * The links survive changes by rules: a node a rule keeps or moves still knows how it was
 * written, a node a rule creates or copies has no link and is printed in the fixed form.
 */
final readonly class SourceMap
{
    /**
     * @param \SplObjectStorage<\DOMNode, SourceNode> $nodes
     */
    private function __construct(
        public \DOMDocument $document,
        public SourceDocument $source,
        private \SplObjectStorage $nodes,
        private string $declaration
    ) {
    }

    /**
     * Returns null if the file cannot be matched to the document, e.g. because it uses entities of a DTD.
     */
    public static function create(string $xml, \DOMDocument $document): ?self
    {
        /** @var \SplObjectStorage<\DOMNode, SourceNode> $nodes */
        $nodes = new \SplObjectStorage();

        try {
            $source = (new SourceParser())->parse($xml);
            self::mapChildren($document, $source->children, $nodes);
        } catch (SourceMismatchException) {
            return null;
        }

        return new self($document, $source, $nodes, self::describeDeclaration($document));
    }

    public function sourceOf(\DOMNode $node): ?SourceNode
    {
        return $this->nodes->contains($node) ? $this->nodes[$node] : null;
    }

    public function isOriginalDocument(\DOMDocument $document): bool
    {
        return $document === $this->document;
    }

    public function hasOriginalDeclaration(\DOMDocument $document): bool
    {
        return $this->isOriginalDocument($document) && self::describeDeclaration($document) === $this->declaration;
    }

    /**
     * @param list<SourceNode> $sourceChildren
     * @param \SplObjectStorage<\DOMNode, SourceNode> $nodes
     */
    private static function mapChildren(\DOMNode $parent, array $sourceChildren, \SplObjectStorage $nodes): void
    {
        $next = 0;
        foreach ($parent->childNodes as $child) {
            if (self::isWhitespaceText($child)) {
                $candidate = $sourceChildren[$next] ?? null;
                if ($candidate instanceof SourceText && $candidate->isWhitespace) {
                    self::link($child, $candidate, $nodes);
                    ++$next;
                }

                continue;
            }

            while (self::isWhitespace($sourceChildren[$next] ?? null)) {
                ++$next;
            }

            $candidate = $sourceChildren[$next] ?? null;
            if ($candidate === null || ! self::matches($child, $candidate)) {
                throw new SourceMismatchException(sprintf('No source found for "%s".', $child->nodeName));
            }

            if ($child instanceof \DOMCdataSection && $candidate instanceof SourceMarkup) {
                [$candidate, $next] = self::joinCdataSections($child, $sourceChildren, $next);
            }

            self::link($child, $candidate, $nodes);
            ++$next;

            if ($child instanceof \DOMElement && $candidate instanceof SourceElement) {
                self::mapChildren($child, $candidate->children, $nodes);
            }
        }

        for ($count = count($sourceChildren); $next < $count; ++$next) {
            if (! self::isWhitespace($sourceChildren[$next])) {
                throw new SourceMismatchException('The file has content the document does not have.');
            }
        }
    }

    /**
     * libxml joins CDATA sections that follow each other, or are only separated by whitespace it drops.
     *
     * @param list<SourceNode> $sourceChildren
     * @return array{SourceMarkup, int} the section to link and the index of its last piece
     */
    private static function joinCdataSections(\DOMCdataSection $cdata, array $sourceChildren, int $first): array
    {
        $firstSection = $sourceChildren[$first];
        if (! $firstSection instanceof SourceMarkup) {
            throw new SourceMismatchException('Expected a CDATA section.');
        }

        $raw = $firstSection->raw;
        $value = $firstSection->value;
        $last = $first;
        while ($value !== $cdata->data) {
            $between = '';
            $next = $last + 1;
            while (($whitespace = $sourceChildren[$next] ?? null) instanceof SourceText && $whitespace->isWhitespace) {
                $between .= $whitespace->raw;
                $whitespace->isMapped = true;
                ++$next;
            }

            $section = $sourceChildren[$next] ?? null;
            if (! $section instanceof SourceMarkup || $section->kind !== SourceMarkup::CDATA) {
                throw new SourceMismatchException('CDATA sections do not match the document.');
            }

            $raw .= $between . $section->raw;
            $value .= $section->value;
            $section->isMapped = true;
            $last = $next;
        }

        if ($last === $first) {
            return [$firstSection, $first];
        }

        $joined = new SourceMarkup(SourceMarkup::CDATA, $raw, $value);
        $joined->parent = $firstSection->parent;
        $joined->index = $firstSection->index;
        $joined->span = $last - $first + 1;

        return [$joined, $last];
    }

    /**
     * @param \SplObjectStorage<\DOMNode, SourceNode> $nodes
     */
    private static function link(\DOMNode $node, SourceNode $source, \SplObjectStorage $nodes): void
    {
        $nodes[$node] = $source;
        $source->isMapped = true;

        if ($node instanceof \DOMCharacterData || $node instanceof \DOMProcessingInstruction) {
            $source->loadedValue = $node->data;
        }

        if ($node instanceof \DOMElement && $source instanceof SourceElement) {
            foreach ($source->attributes as $attribute) {
                $value = match (true) {
                    $attribute->name === 'xmlns:xml' => XmlEscaper::decodeAttribute($attribute->rawValue),
                    $node->hasAttribute($attribute->name) => $node->getAttribute($attribute->name),
                    default => null,
                };
                if ($value !== null) {
                    $source->loadedAttributes[$attribute->name] = $value;
                }
            }
        }
    }

    private static function matches(\DOMNode $node, SourceNode $source): bool
    {
        return match (true) {
            $node instanceof \DOMElement => $source instanceof SourceElement && $source->name === $node->nodeName,
            $node instanceof \DOMCdataSection => self::isMarkup($source, SourceMarkup::CDATA),
            $node instanceof \DOMText => $source instanceof SourceText && ! $source->isWhitespace,
            $node instanceof \DOMComment => self::isMarkup($source, SourceMarkup::COMMENT),
            $node instanceof \DOMProcessingInstruction => self::isMarkup($source, SourceMarkup::PROCESSING_INSTRUCTION),
            $node instanceof \DOMDocumentType => self::isMarkup($source, SourceMarkup::DOCUMENT_TYPE),
            default => false,
        };
    }

    private static function isMarkup(SourceNode $source, string $kind): bool
    {
        return $source instanceof SourceMarkup && $source->kind === $kind;
    }

    private static function isWhitespace(?SourceNode $source): bool
    {
        return $source instanceof SourceText && $source->isWhitespace;
    }

    private static function isWhitespaceText(\DOMNode $node): bool
    {
        return $node instanceof \DOMText
            && ! $node instanceof \DOMCdataSection
            && XmlEscaper::isWhitespace($node->data);
    }

    private static function describeDeclaration(\DOMDocument $document): string
    {
        return sprintf(
            '%s|%s|%s',
            $document->xmlVersion,
            $document->xmlEncoding,
            $document->xmlStandalone ? 'yes' : 'no'
        );
    }
}
