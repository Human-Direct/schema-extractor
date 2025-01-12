<?php

declare(strict_types=1);

namespace HumanDirect\SchemaExtractor\Contracts;

/**
 * @template T of object
 */
interface SchemaParserInterface
{
    /**
     * @param array<string, mixed> $data
     *
     * @return T|null
     */
    public function parse(array $data, string $type): ?object;
}
