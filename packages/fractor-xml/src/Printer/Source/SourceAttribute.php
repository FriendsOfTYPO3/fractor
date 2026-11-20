<?php

declare(strict_types=1);

namespace a9f\FractorXml\Printer\Source;

final readonly class SourceAttribute
{
    /**
     * @param string $raw the attribute as written, e.g. title='It&apos;s'
     * @param string $rawValue the value as written, without quotes
     */
    public function __construct(
        public string $name,
        public string $raw,
        public string $rawValue,
        public string $quote,
        public string $leadingWhitespace
    ) {
    }

    public function isOnNewLine(): bool
    {
        return str_contains($this->leadingWhitespace, "\n");
    }

    public function isNamespaceDeclaration(): bool
    {
        return $this->name === 'xmlns' || str_starts_with($this->name, 'xmlns:');
    }
}
