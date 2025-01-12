<?php

declare(strict_types=1);

namespace HumanDirect\SchemaExtractor\Dto;

use HumanDirect\SchemaExtractor\Contracts\SchemaTypeInterface;
use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class Organization implements SchemaTypeInterface
{
    public function __construct(
        #[SerializedName('name')]
        public string $name,

        #[SerializedName('logo')]
        public ?string $logo = null,

        #[SerializedName('url')]
        public ?string $url = null,
    ) {
    }

    public function getType(): string
    {
        return 'Organization';
    }
}
