<?php

declare(strict_types=1);

namespace HumanDirect\SchemaExtractor\Contracts;

use HumanDirect\SchemaExtractor\Exception\ExtractionException;

/**
 * @template T of object
 */
interface JsonLdExtractorInterface
{
    /**
     * Extract structured data from HTML content.
     *
     * @param string $html Raw HTML content
     * @param array<string> $supportedTypes List of supported Schema.org types (without namespace)
     *
     * @throws ExtractionException
     *
     * @return T|null
     */
    public function extract(string $html, array $supportedTypes = []): ?object;
}
