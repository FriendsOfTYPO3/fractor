<?php

declare(strict_types=1);

namespace a9f\FractorXml\Printer\Source;

/**
 * A comment, CDATA section, processing instruction or document type declaration.
 */
final class SourceMarkup extends SourceNode
{
    public const COMMENT = 'comment';

    public const CDATA = 'cdata';

    public const PROCESSING_INSTRUCTION = 'processing-instruction';

    public const DOCUMENT_TYPE = 'document-type';

    /**
     * @param self::* $kind
     * @param string $value the content of a CDATA section, libxml joins neighbouring sections into one
     */
    public function __construct(
        public readonly string $kind,
        public readonly string $raw,
        public readonly string $value = ''
    ) {
    }

    public static function cdata(string $raw): self
    {
        return new self(self::CDATA, $raw, str_replace(["\r\n", "\r"], "\n", substr($raw, 9, -3)));
    }
}
