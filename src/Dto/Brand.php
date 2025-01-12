<?php

declare(strict_types=1);

namespace HumanDirect\SchemaExtractor\Dto;

use HumanDirect\SchemaExtractor\Contracts\SchemaTypeInterface;
use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class Brand implements SchemaTypeInterface
{
    public function __construct(
        #[SerializedName('name')]
        public string $name,
    ) {
    }

    public function getType(): string
    {
        return 'Brand';
    }
}
