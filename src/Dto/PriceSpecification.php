<?php

declare(strict_types=1);

namespace HumanDirect\SchemaExtractor\Dto;

use HumanDirect\SchemaExtractor\Contracts\SchemaTypeInterface;
use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class PriceSpecification implements SchemaTypeInterface
{
    public function __construct(
        #[SerializedName('price')]
        public float $price,

        #[SerializedName('priceCurrency')]
        public string $priceCurrency,

        #[SerializedName('valueAddedTaxIncluded')]
        public ?bool $valueAddedTaxIncluded = null,
    ) {
    }

    public function getType(): string
    {
        return 'PriceSpecification';
    }
}
