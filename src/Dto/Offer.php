<?php

declare(strict_types=1);

namespace HumanDirect\SchemaExtractor\Dto;

use DateTimeImmutable;
use HumanDirect\SchemaExtractor\Contracts\SchemaTypeInterface;
use HumanDirect\SchemaExtractor\Enum\Availability;
use HumanDirect\SchemaExtractor\Enum\ItemCondition;
use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class Offer implements SchemaTypeInterface
{
    public function __construct(
        #[SerializedName('price')]
        public float $price,

        #[SerializedName('priceCurrency')]
        public string $priceCurrency,

        #[SerializedName('priceValidUntil')]
        public ?DateTimeImmutable $priceValidUntil = null,

        #[SerializedName('itemCondition')]
        public ?ItemCondition $itemCondition = null,

        #[SerializedName('availability')]
        public ?Availability $availability = null,

        #[SerializedName('url')]
        public ?string $url = null,

        #[SerializedName('priceSpecification')]
        public ?PriceSpecification $priceSpecification = null,

        #[SerializedName('seller')]
        public ?Organization $seller = null,
    ) {
    }

    public function getType(): string
    {
        return 'Offer';
    }
}
