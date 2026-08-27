<?php

declare(strict_types=1);

namespace a9f\FractorXliff;

use a9f\Fractor\Exception\UnparsableFileException;

final class DomDocumentFactory
{
    public function create(): \DOMDocument
    {
        $document = new \DOMDocument();
        $document->preserveWhiteSpace = false;
        $document->formatOutput = true;

        return $document;
    }

    /**
     * Loads XML into a fresh document, or fails with the reason libxml reported.
     *
     * Callers must never fall back to an unloaded document: it serializes to a
     * bare XML declaration, which would silently truncate the file on write.
     */
    public function createFromXml(string $xml): \DOMDocument
    {
        // An empty string makes loadXML() throw a ValueError instead of
        // reporting a parse error, so it is rejected up front.
        if ($xml === '') {
            throw new UnparsableFileException('The file is empty.');
        }

        $document = $this->create();

        // Collect libxml's diagnostics instead of letting them leak to stdout
        // as raw PHP warnings, and leave the global state as it was found.
        $previousUseInternalErrors = libxml_use_internal_errors(true);
        libxml_clear_errors();

        try {
            $isLoaded = $document->loadXML($xml);
            $errors = libxml_get_errors();
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousUseInternalErrors);
        }

        // Warnings such as an invalid xml:space value leave a usable document,
        // so only a document that failed to load is rejected.
        if ($isLoaded && $document->documentElement !== null) {
            return $document;
        }

        throw new UnparsableFileException($this->describeErrors($errors));
    }

    /**
     * @param \LibXMLError[] $errors
     */
    private function describeErrors(array $errors): string
    {
        $messages = [];
        foreach ($errors as $error) {
            if ($error->level === LIBXML_ERR_WARNING) {
                continue;
            }

            $messages[] = sprintf('line %d: %s', $error->line, trim($error->message));
        }

        if ($messages === []) {
            return 'The file is not well-formed XML.';
        }

        return 'The file is not well-formed XML (' . implode('; ', array_unique($messages)) . ').';
    }
}
