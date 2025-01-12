<?php

declare(strict_types=1);

namespace HumanDirect\SchemaExtractor\Dto;

use HumanDirect\SchemaExtractor\Contracts\SchemaTypeInterface;
use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class Author implements SchemaTypeInterface
{
    public function __construct(
        #[SerializedName('name')]
        public string $name,

        #[SerializedName('image')]
        public ?string $image = null,
    ) {
    }

    public function getType(): string
    {
        return 'Person';
    }
}
