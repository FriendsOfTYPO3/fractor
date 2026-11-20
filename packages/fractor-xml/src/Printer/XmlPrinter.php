<?php

declare(strict_types=1);

namespace a9f\FractorXml\Printer;

use a9f\Fractor\ValueObject\Indent;

/**
 * Prints a document the way its author wrote it, with the configured indentation.
 *
 * Nodes linked to the file keep their layout: attributes on one line or on several lines, their
 * order, quotes and entities, empty elements, blank lines, comments and any text content. Nodes
 * a rule created are printed in a fixed form.
 */
final class XmlPrinter
{
    /**
     * Raw pieces of the file are copied byte by byte, so they must share the encoding of
     * the values libxml reports.
     */
    public function canPrint(\DOMDocument $document): bool
    {
        $encoding = strtolower((string) $document->xmlEncoding);

        return in_array($encoding, ['', 'utf-8', 'utf8'], true);
    }

    public function print(\DOMDocument $document, Indent $indent, ?SourceMap $sourceMap = null): string
    {
        return (new DocumentPrinter($document, $indent->toString(), $sourceMap))->print();
    }
}
