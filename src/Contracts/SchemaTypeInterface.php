<?php

declare(strict_types=1);

namespace HumanDirect\SchemaExtractor\Contracts;

interface SchemaTypeInterface
{
    /**
     * Get the Schema.org type.
     */
    public function getType(): string;
}
