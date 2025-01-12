<?php

declare(strict_types=1);

namespace HumanDirect\SchemaExtractor\Dto;

use HumanDirect\SchemaExtractor\Contracts\SchemaTypeInterface;
use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class AggregateRating implements SchemaTypeInterface
{
    public function __construct(
        #[SerializedName('ratingValue')]
        public float $ratingValue,

        #[SerializedName('reviewCount')]
        public int $reviewCount,

        #[SerializedName('ratingCount')]
        public int $ratingCount,

        #[SerializedName('bestRating')]
        public int $bestRating = 5,

        #[SerializedName('worstRating')]
        public int $worstRating = 1,
    ) {
    }

    public function getType(): string
    {
        return 'AggregateRating';
    }
}
