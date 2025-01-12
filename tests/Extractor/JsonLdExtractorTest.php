<?php

declare(strict_types=1);

namespace HumanDirect\SchemaExtractor\Tests\Extractor;

use DateTimeImmutable;
use HumanDirect\SchemaExtractor\Contracts\SchemaParserInterface;
use HumanDirect\SchemaExtractor\Contracts\SchemaTypeInterface;
use HumanDirect\SchemaExtractor\Dto\AggregateRating;
use HumanDirect\SchemaExtractor\Dto\Author;
use HumanDirect\SchemaExtractor\Dto\Brand;
use HumanDirect\SchemaExtractor\Dto\Offer;
use HumanDirect\SchemaExtractor\Dto\Organization;
use HumanDirect\SchemaExtractor\Dto\PriceSpecification;
use HumanDirect\SchemaExtractor\Dto\Product;
use HumanDirect\SchemaExtractor\Dto\Rating;
use HumanDirect\SchemaExtractor\Dto\Review;
use HumanDirect\SchemaExtractor\Enum\Availability;
use HumanDirect\SchemaExtractor\Enum\ItemCondition;
use HumanDirect\SchemaExtractor\Exception\ExtractionException;
use HumanDirect\SchemaExtractor\Extractor\JsonLdExtractor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(JsonLdExtractor::class)]
final class JsonLdExtractorTest extends TestCase
{
    private JsonLdExtractor $extractor;

    /** @var SchemaParserInterface<SchemaTypeInterface>&MockObject */
    private SchemaParserInterface $parser;

    #[Test]
    public function extractShouldReturnNullWhenNoScriptsFound(): void
    {
        $html = '<html><body>No scripts here</body></html>';

        $result = $this->extractor->extract($html);

        self::assertNull($result);
    }

    #[Test]
    public function extractShouldHandleSingleProduct(): void
    {
        $html = <<<HTML
            <html>
                <script type="application/ld+json">
                {
                    "@context": "https://schema.org",
                    "@type": "Product",
                    "name": "Test Product",
                    "description": "Test description",
                    "brand": {
                        "@type": "Brand",
                        "name": "Test Brand"
                    },
                    "offers": [
                        {
                            "@type": "Offer",
                            "price": 99.99,
                            "priceCurrency": "USD"
                        }
                    ]
                }
                </script>
            </html>
            HTML;

        $expectedBrand = new Brand(name: 'Test Brand');
        $expectedOffer = new Offer(
            price: 99.99,
            priceCurrency: 'USD',
        );

        $expectedProduct = new Product(
            name: 'Test Product',
            description: 'Test description',
            brand: $expectedBrand,
            offers: [$expectedOffer],
        );

        $this->parser
            ->expects(self::once())
            ->method('parse')
            ->with(
                self::callback(static function (array $data): bool {
                    return $data['name'] === 'Test Product'
                        && $data['description'] === 'Test description'
                        && $data['brand']['name'] === 'Test Brand'
                        && $data['offers'][0]['price'] === 99.99
                        && $data['offers'][0]['priceCurrency'] === 'USD';
                }),
                'Product',
            )
            ->willReturn($expectedProduct);

        $result = $this->extractor->extract($html);

        self::assertInstanceOf(Product::class, $result);
        self::assertEquals($expectedProduct, $result);
        self::assertSame('Test Product', $result->name);
        self::assertSame('Test description', $result->description);
        self::assertInstanceOf(Brand::class, $result->brand);
        self::assertSame('Test Brand', $result->brand->name);
        self::assertCount(1, $result->offers);
        self::assertInstanceOf(Offer::class, $result->offers[0]);
        self::assertSame(99.99, $result->offers[0]->price);
        self::assertSame('USD', $result->offers[0]->priceCurrency);
    }

    #[Test]
    public function extractShouldHandleMinimalProduct(): void
    {
        $html = <<<HTML
            <html>
                <script type="application/ld+json">
                {
                    "@context": "https://schema.org",
                    "@type": "Product",
                    "name": "Minimal Product"
                }
                </script>
            </html>
            HTML;

        $expectedProduct = new Product(name: 'Minimal Product');

        $this->parser
            ->expects(self::once())
            ->method('parse')
            ->with(
                self::callback(static function (array $data): bool {
                    return $data['name'] === 'Minimal Product'
                        && !isset($data['description'])
                        && !isset($data['brand'])
                        && !isset($data['offers'])
                        && !isset($data['image']);
                }),
                'Product',
            )
            ->willReturn($expectedProduct);

        $result = $this->extractor->extract($html);

        self::assertInstanceOf(Product::class, $result);
        self::assertEquals($expectedProduct, $result);
        self::assertSame('Minimal Product', $result->name);
        self::assertNull($result->description);
        self::assertNull($result->brand);
        self::assertNull($result->aggregateRating);
        self::assertEmpty($result->image);
        self::assertEmpty($result->offers);
    }

    #[Test]
    public function extractShouldHandleCompleteProduct(): void
    {
        $html = <<<HTML
            <html>
                <script type="application/ld+json">
                {
                    "@context": "https://schema.org",
                    "@type": "Product",
                    "@id": "https://example.com/product/123",
                    "name": "Test Complete Product",
                    "url": "https://example.com/product/123",
                    "description": "Test description for complete product",
                    "image": [
                        "https://example.com/img1.jpg",
                        "https://example.com/img2.jpg"
                    ],
                    "sku": "TEST123",
                    "gtin": "123456789",
                    "gtin8": "12345678",
                    "gtin13": "1234567890123",
                    "gtin14": "12345678901234",
                    "mpn": "MPN123",
                    "productID": "PROD123",
                    "color": "Red",
                    "depth": "10 cm",
                    "height": "20 cm",
                    "width": "15 cm",
                    "weight": "1.5 kg",
                    "material": "Cotton",
                    "category": ["Clothing", "T-Shirts"],
                    "additionalProperty": ["Organic", "Fair Trade"],
                    "itemCondition": "https://schema.org/NewCondition",
                    "brand": {
                        "@type": "Brand",
                        "name": "Test Brand"
                    },
                    "manufacturer": {
                        "@type": "Organization",
                        "name": "Test Manufacturer",
                        "url": "https://manufacturer.example.com",
                        "logo": "https://manufacturer.example.com/logo.png"
                    },
                    "aggregateRating": {
                        "@type": "AggregateRating",
                        "ratingValue": 4.7,
                        "reviewCount": 123,
                        "bestRating": 5,
                        "worstRating": 1
                    },
                    "review": [
                        {
                            "@type": "Review",
                            "author": {
                                "@type": "Person",
                                "name": "John Doe",
                                "image": "https://example.com/author.jpg"
                            },
                            "reviewRating": {
                                "@type": "Rating",
                                "ratingValue": 5,
                                "bestRating": 5,
                                "worstRating": 1
                            },
                            "reviewBody": "Great product!",
                            "datePublished": "2024-01-15"
                        }
                    ],
                    "offers": [
                        {
                            "@type": "Offer",
                            "price": 29.99,
                            "priceCurrency": "USD",
                            "priceValidUntil": "2024-12-31",
                            "url": "https://example.com/product/123/offer",
                            "itemCondition": "https://schema.org/NewCondition",
                            "availability": "https://schema.org/InStock",
                            "priceSpecification": {
                                "@type": "PriceSpecification",
                                "price": 29.99,
                                "priceCurrency": "USD",
                                "valueAddedTaxIncluded": "https://schema.org/True"
                            },
                            "seller": {
                                "@type": "Organization",
                                "name": "Test Seller",
                                "url": "https://seller.example.com",
                                "logo": "https://seller.example.com/logo.png"
                            }
                        }
                    ]
                }
                </script>
            </html>
            HTML;

        $expectedAuthor = new Author(
            name: 'John Doe',
            image: 'https://example.com/author.jpg',
        );

        $expectedRating = new Rating(
            ratingValue: 5.0,
            bestRating: 5,
            worstRating: 1,
        );

        $expectedReviewDate = new DateTimeImmutable('2024-01-15');
        $expectedReview = new Review(
            author: $expectedAuthor,
            rating: $expectedRating,
            reviewBody: 'Great product!',
            datePublished: $expectedReviewDate,
        );

        $expectedBrand = new Brand(name: 'Test Brand');

        $expectedManufacturer = new Organization(
            name: 'Test Manufacturer',
            logo: 'https://manufacturer.example.com/logo.png',
            url: 'https://manufacturer.example.com',
        );

        $expectedAggregateRating = new AggregateRating(
            ratingValue: 4.7,
            reviewCount: 123,
            ratingCount: 328,
            bestRating: 5,
            worstRating: 1,
        );

        $expectedPriceSpec = new PriceSpecification(
            price: 29.99,
            priceCurrency: 'USD',
            valueAddedTaxIncluded: true,
        );

        $expectedSeller = new Organization(
            name: 'Test Seller',
            logo: 'https://seller.example.com/logo.png',
            url: 'https://seller.example.com',
        );

        $expectedOfferPriceValidUntil = new DateTimeImmutable('2024-12-31');
        $expectedOffer = new Offer(
            price: 29.99,
            priceCurrency: 'USD',
            priceValidUntil: $expectedOfferPriceValidUntil,
            itemCondition: ItemCondition::NewCondition,
            availability: Availability::InStock,
            url: 'https://example.com/product/123/offer',
            priceSpecification: $expectedPriceSpec,
            seller: $expectedSeller,
        );

        $expectedProduct = new Product(
            name: 'Test Complete Product',
            id: 'https://example.com/product/123',
            url: 'https://example.com/product/123',
            image: [
                'https://example.com/img1.jpg',
                'https://example.com/img2.jpg',
            ],
            description: 'Test description for complete product',
            brand: $expectedBrand,
            manufacturer: $expectedManufacturer,
            model: null,
            sku: 'TEST123',
            gtin: '123456789',
            gtin8: '12345678',
            gtin13: '1234567890123',
            gtin14: '12345678901234',
            mpn: 'MPN123',
            productId: 'PROD123',
            color: 'Red',
            depth: '10 cm',
            height: '20 cm',
            width: '15 cm',
            weight: '1.5 kg',
            material: 'Cotton',
            category: ['Clothing', 'T-Shirts'],
            additionalProperty: ['Organic', 'Fair Trade'],
            aggregateRating: $expectedAggregateRating,
            reviews: [$expectedReview],
            offers: [$expectedOffer],
            itemCondition: ItemCondition::NewCondition,
        );

        $this->parser
            ->expects(self::once())
            ->method('parse')
            ->with(
                self::callback(static function (array $data): bool {
                    $identifiers = $data['@id'] === 'https://example.com/product/123'
                        && $data['sku'] === 'TEST123'
                        && $data['gtin'] === '123456789'
                        && $data['gtin8'] === '12345678'
                        && $data['gtin13'] === '1234567890123'
                        && $data['gtin14'] === '12345678901234'
                        && $data['mpn'] === 'MPN123'
                        && $data['productID'] === 'PROD123';

                    $dimensions = $data['color'] === 'Red'
                        && $data['depth'] === '10 cm'
                        && $data['height'] === '20 cm'
                        && $data['width'] === '15 cm'
                        && $data['weight'] === '1.5 kg'
                        && $data['material'] === 'Cotton';

                    $categorization = $data['category'] === ['Clothing', 'T-Shirts']
                        && $data['additionalProperty'] === ['Organic', 'Fair Trade'];

                    $rating = $data['aggregateRating']['ratingValue'] === 4.7
                        && $data['aggregateRating']['reviewCount'] === 123
                        && $data['aggregateRating']['bestRating'] === 5
                        && $data['aggregateRating']['worstRating'] === 1;

                    $review = $data['review'][0]['author']['name'] === 'John Doe'
                        && $data['review'][0]['reviewRating']['ratingValue'] === 5
                        && $data['review'][0]['reviewBody'] === 'Great product!';

                    $offer = $data['offers'][0]['price'] === 29.99
                        && $data['offers'][0]['priceValidUntil'] === '2024-12-31'
                        && $data['offers'][0]['priceSpecification']['valueAddedTaxIncluded'] === 'https://schema.org/True';

                    return $identifiers && $dimensions && $categorization && $rating && $review && $offer;
                }),
                'Product',
            )
            ->willReturn($expectedProduct);

        $result = $this->extractor->extract($html);

        self::assertInstanceOf(Product::class, $result);
        self::assertEquals($expectedProduct, $result);

        // ID and Basic Info
        self::assertSame('https://example.com/product/123', $result->id);
        self::assertSame('Test Complete Product', $result->name);
        self::assertSame('https://example.com/product/123', $result->url);
        self::assertSame('Test description for complete product', $result->description);

        // Images and Media
        self::assertCount(2, $result->image);
        self::assertSame(['https://example.com/img1.jpg', 'https://example.com/img2.jpg'], $result->image);

        // Identifiers
        self::assertSame('TEST123', $result->sku);
        self::assertSame('123456789', $result->gtin);
        self::assertSame('12345678', $result->gtin8);
        self::assertSame('1234567890123', $result->gtin13);
        self::assertSame('12345678901234', $result->gtin14);
        self::assertSame('MPN123', $result->mpn);
        self::assertSame('PROD123', $result->productId);

        // Physical Properties
        self::assertSame('Red', $result->color);
        self::assertSame('10 cm', $result->depth);
        self::assertSame('20 cm', $result->height);
        self::assertSame('15 cm', $result->width);
        self::assertSame('1.5 kg', $result->weight);
        self::assertSame('Cotton', $result->material);

        // Categories and Properties
        self::assertSame(['Clothing', 'T-Shirts'], $result->category);
        self::assertSame(['Organic', 'Fair Trade'], $result->additionalProperty);
        self::assertSame(ItemCondition::NewCondition, $result->itemCondition);

        // Brand and Manufacturer
        self::assertInstanceOf(Brand::class, $result->brand);
        self::assertSame('Test Brand', $result->brand->name);
        self::assertInstanceOf(Organization::class, $result->manufacturer);
        self::assertSame('Test Manufacturer', $result->manufacturer->name);
        self::assertSame('https://manufacturer.example.com', $result->manufacturer->url);
        self::assertSame('https://manufacturer.example.com/logo.png', $result->manufacturer->logo);

        // Ratings and Reviews
        self::assertInstanceOf(AggregateRating::class, $result->aggregateRating);
        self::assertSame(4.7, $result->aggregateRating->ratingValue);
        self::assertSame(123, $result->aggregateRating->reviewCount);
        self::assertSame(5, $result->aggregateRating->bestRating);
        self::assertSame(1, $result->aggregateRating->worstRating);

        self::assertCount(1, $result->reviews);
        self::assertInstanceOf(Review::class, $result->reviews[0]);
        self::assertInstanceOf(Author::class, $result->reviews[0]->author);
        self::assertSame('John Doe', $result->reviews[0]->author->name);
        self::assertSame('https://example.com/author.jpg', $result->reviews[0]->author->image);
        self::assertInstanceOf(Rating::class, $result->reviews[0]->rating);
        self::assertSame(5.0, $result->reviews[0]->rating->ratingValue);
        self::assertSame('Great product!', $result->reviews[0]->reviewBody);
        self::assertSame($expectedReviewDate, $result->reviews[0]->datePublished);

        // Offers
        self::assertCount(1, $result->offers);
        self::assertInstanceOf(Offer::class, $result->offers[0]);
        self::assertSame(29.99, $result->offers[0]->price);
        self::assertSame('USD', $result->offers[0]->priceCurrency);
        self::assertSame($expectedOfferPriceValidUntil, $result->offers[0]->priceValidUntil);
        self::assertSame('https://example.com/product/123/offer', $result->offers[0]->url);
        self::assertSame(ItemCondition::NewCondition, $result->offers[0]->itemCondition);
        self::assertSame(Availability::InStock, $result->offers[0]->availability);

        self::assertInstanceOf(PriceSpecification::class, $result->offers[0]->priceSpecification);
        self::assertSame(29.99, $result->offers[0]->priceSpecification->price);
        self::assertSame('USD', $result->offers[0]->priceSpecification->priceCurrency);
        self::assertTrue($result->offers[0]->priceSpecification->valueAddedTaxIncluded);

        self::assertInstanceOf(Organization::class, $result->offers[0]->seller);
        self::assertSame('Test Seller', $result->offers[0]->seller->name);
        self::assertSame('https://seller.example.com', $result->offers[0]->seller->url);
        self::assertSame('https://seller.example.com/logo.png', $result->offers[0]->seller->logo);
    }

    #[Test]
    public function extractShouldHandleGraphData(): void
    {
        $html = <<<HTML
            <html>
                <script type="application/ld+json">
                {
                    "@context": "https://schema.org",
                    "@graph": [
                        {
                            "@type": "WebSite",
                            "name": "Test Site"
                        },
                        {
                            "@type": "Product",
                            "name": "Test Product",
                            "description": "Test Description",
                            "brand": {
                                "@type": "Brand",
                                "name": "Test Brand"
                            }
                        }
                    ]
                }
                </script>
            </html>
            HTML;

        $expectedProduct = new Product(
            name: 'Test Product',
            description: 'Test Description',
            brand: new Brand(name: 'Test Brand'),
        );

        $this->parser
            ->expects(self::exactly(2))
            ->method('parse')
            ->willReturnCallback(static function (array $data, string $type) use ($expectedProduct): ?object {
                if ($type === 'Product') {
                    return $expectedProduct;
                }

                return null;
            });

        $result = $this->extractor->extract($html);

        self::assertEquals($expectedProduct, $result);
    }

    #[Test]
    public function extractShouldFilterBySupportedTypes(): void
    {
        $html = <<<HTML
            <html>
                <script type="application/ld+json">
                {
                    "@context": "https://schema.org",
                    "@type": "Product",
                    "name": "Test Product"
                }
                </script>
            </html>
            HTML;

        $this->parser
            ->expects(self::never())
            ->method('parse');

        $result = $this->extractor->extract($html, ['Article']);

        self::assertNull($result);
    }

    #[Test]
    #[DataProvider('provideInvalidHtml')]
    public function extractShouldHandleInvalidHtml(string $html): void
    {
        $result = $this->extractor->extract($html);
        self::assertNull($result);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function provideInvalidHtml(): array
    {
        return [
            'unclosed tags' => ['<html><div><p>Unclosed tags<div>Mismatched nesting</p></div>'],
            'truncated html' => ['<html><scr'],
            'null byte' => ["Invalid\0HTML"],
            'empty html' => [''],
        ];
    }

    #[Test]
    #[DataProvider('provideMalformedSchema')]
    public function extractShouldThrowExceptionOnInvalidJson(string $html): void
    {
        $this->expectException(ExtractionException::class);
        $this->expectExceptionMessage('Failed to extract JSON-LD data: Syntax error');

        $this->extractor->extract($html);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function provideMalformedSchema(): array
    {
        return [
            'malformed script' => ['<html><script type="application/ld+json">{ invalid json }</script></html>'],
            'unclosed script' => ['<html><script type="application/ld+json">{"@type": "Product"</script></html>'],
        ];
    }

    protected function setUp(): void
    {
        $this->parser = $this->createMock(SchemaParserInterface::class);
        $this->extractor = new JsonLdExtractor($this->parser);
    }
}
