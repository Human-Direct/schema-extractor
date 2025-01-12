<?php

declare(strict_types=1);

namespace HumanDirect\SchemaExtractor\Dto;

use HumanDirect\SchemaExtractor\Contracts\SchemaTypeInterface;
use HumanDirect\SchemaExtractor\Enum\ItemCondition;
use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class Product implements SchemaTypeInterface
{
    /**
     * @param string[] $image
     * @param string[] $category
     * @param string[] $additionalProperty
     * @param Offer[] $offers
     * @param Review[] $reviews
     */
    public function __construct(
        #[SerializedName('name')]
        public string $name,

        #[SerializedName('@id')]
        public ?string $id = null,

        #[SerializedName('url')]
        public ?string $url = null,

        #[SerializedName('image')]
        public array $image = [],

        #[SerializedName('description')]
        public ?string $description = null,

        #[SerializedName('brand')]
        public ?Brand $brand = null,

        #[SerializedName('manufacturer')]
        public ?Organization $manufacturer = null,

        #[SerializedName('model')]
        public ?string $model = null,

        #[SerializedName('sku')]
        public ?string $sku = null,

        #[SerializedName('gtin')]
        public ?string $gtin = null,

        #[SerializedName('gtin8')]
        public ?string $gtin8 = null,

        #[SerializedName('gtin13')]
        public ?string $gtin13 = null,

        #[SerializedName('gtin14')]
        public ?string $gtin14 = null,

        #[SerializedName('mpn')]
        public ?string $mpn = null,

        #[SerializedName('productID')]
        public ?string $productId = null,

        #[SerializedName('color')]
        public ?string $color = null,

        #[SerializedName('depth')]
        public ?string $depth = null,

        #[SerializedName('height')]
        public ?string $height = null,

        #[SerializedName('width')]
        public ?string $width = null,

        #[SerializedName('weight')]
        public ?string $weight = null,

        #[SerializedName('material')]
        public ?string $material = null,

        #[SerializedName('category')]
        public array $category = [],

        #[SerializedName('additionalProperty')]
        public array $additionalProperty = [],

        #[SerializedName('aggregateRating')]
        public ?AggregateRating $aggregateRating = null,

        #[SerializedName('review')]
        public array $reviews = [],

        #[SerializedName('offers')]
        public array $offers = [],

        #[SerializedName('itemCondition')]
        public ?ItemCondition $itemCondition = null,
    ) {
    }

    public function getType(): string
    {
        return 'Product';
    }
}
