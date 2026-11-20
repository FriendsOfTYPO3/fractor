<?php

declare(strict_types=1);

namespace a9f\FractorXml\Printer\Source;

/**
 * A piece of the original file, as the author wrote it.
 */
abstract class SourceNode
{
    public SourceElement|SourceDocument|null $parent = null;

    public int $index = 0;

    /**
     * Number of siblings this piece covers, more than one for CDATA sections libxml joined.
     */
    public int $span = 1;

    /**
     * Whether a DOM node was created from this piece; libxml drops most
     * whitespace between elements, so those pieces have no DOM node.
     */
    public bool $isMapped = false;

    /**
     * The value libxml reported when the file was loaded; a node still holding it is unchanged.
     */
    public ?string $loadedValue = null;
}
