<?php

declare(strict_types=1);

namespace a9f\FractorXml\Printer;

/**
 * Converts between text as written in a file and the value libxml reports.
 */
final class XmlEscaper
{
    public static function decodeText(string $raw): string
    {
        return html_entity_decode(str_replace(["\r\n", "\r"], "\n", $raw), ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    /**
     * Applies the attribute value normalization of the XML specification:
     * line breaks and tabs written as such become spaces, character references keep them.
     */
    public static function decodeAttribute(string $raw): string
    {
        $normalized = str_replace(["\r\n", "\r", "\n", "\t"], ' ', $raw);

        return html_entity_decode($normalized, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    public static function escapeText(string $value): string
    {
        return strtr($value, [
            '&' => '&amp;',
            '<' => '&lt;',
            ']]>' => ']]&gt;',
            "\r" => '&#13;',
        ]);
    }

    public static function escapeAttribute(string $value, string $quote): string
    {
        return strtr($value, [
            '&' => '&amp;',
            '<' => '&lt;',
            $quote => $quote === '"' ? '&quot;' : '&apos;',
            "\n" => '&#10;',
            "\r" => '&#13;',
            "\t" => '&#9;',
        ]);
    }

    public static function attribute(string $name, string $value, string $quote = '"'): string
    {
        return $name . '=' . $quote . self::escapeAttribute($value, $quote) . $quote;
    }

    public static function isWhitespace(string $value): bool
    {
        return trim($value, " \t\n\r") === '';
    }
}
