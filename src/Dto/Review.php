<?php

declare(strict_types=1);

namespace HumanDirect\SchemaExtractor\Dto;

use DateTimeImmutable;
use HumanDirect\SchemaExtractor\Contracts\SchemaTypeInterface;
use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class Review implements SchemaTypeInterface
{
    public function __construct(
        #[SerializedName('author')]
        public ?Author $author = null,

        #[SerializedName('reviewRating')]
        public ?Rating $rating = null,

        #[SerializedName('reviewBody')]
        public ?string $reviewBody = null,

        #[SerializedName('datePublished')]
        public ?DateTimeImmutable $datePublished = null,
    ) {
    }

    public function getType(): string
    {
        return 'Review';
    }
}
