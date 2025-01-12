<?php

declare(strict_types=1);

namespace HumanDirect\SchemaExtractor\Extractor;

use HumanDirect\SchemaExtractor\Contracts\JsonLdExtractorInterface;
use HumanDirect\SchemaExtractor\Contracts\SchemaParserInterface;
use HumanDirect\SchemaExtractor\Contracts\SchemaTypeInterface;
use HumanDirect\SchemaExtractor\Exception\ExtractionException;
use Symfony\Component\DomCrawler\Crawler;
use Throwable;

/**
 * @template-implements JsonLdExtractorInterface<SchemaTypeInterface>
 */
final readonly class JsonLdExtractor implements JsonLdExtractorInterface
{
    /**
     * @param SchemaParserInterface<SchemaTypeInterface> $parser
     */
    public function __construct(
        private SchemaParserInterface $parser,
    ) {
    }

    public function extract(string $html, array $supportedTypes = []): ?SchemaTypeInterface
    {
        try {
            // First validate HTML by attempting to create a crawler
            new Crawler($html);

            $crawler = new Crawler($html);
            $scripts = $crawler->filter('script[type="application/ld+json"]');

            if ($scripts->count() === 0) {
                return null;
            }

            foreach ($scripts as $script) {
                $content = $script->textContent;

                if ($content === '') {
                    continue;
                }

                $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

                if (!is_array($data)) {
                    continue;
                }

                // Handle @graph notation
                if (isset($data['@graph']) && is_array($data['@graph'])) {
                    /** @var array<string, mixed> $item */
                    foreach ($data['@graph'] as $item) {
                        if (!is_array($item)) {
                            continue;
                        }

                        $result = $this->processItem($item, $supportedTypes);

                        if ($result !== null) {
                            return $result;
                        }
                    }
                } else {
                    /** @var array<string, mixed> $data */
                    $result = $this->processItem($data, $supportedTypes);

                    if ($result !== null) {
                        return $result;
                    }
                }
            }

            return null;
        } catch (Throwable $e) {
            throw new ExtractionException(sprintf('Failed to extract JSON-LD data: %s', $e->getMessage()), previous: $e);
        }
    }

    /**
     * @param array<string, mixed> $item
     * @param array<string> $supportedTypes
     */
    private function processItem(array $item, array $supportedTypes): ?SchemaTypeInterface
    {
        if (!isset($item['@type'])) {
            return null;
        }

        $type = is_string($item['@type']) ? basename($item['@type']) : null;

        if ($type === null) {
            return null;
        }

        // If supported types are specified, check if current type is supported
        if ($supportedTypes !== [] && !in_array($type, $supportedTypes, true)) {
            return null;
        }

        return $this->parser->parse($item, $type);
    }
}
