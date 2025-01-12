<?php

declare(strict_types=1);

namespace HumanDirect\SchemaExtractor\Extractor;

use HumanDirect\SchemaExtractor\Contracts\SchemaParserInterface;
use HumanDirect\SchemaExtractor\Contracts\SchemaTypeInterface;
use HumanDirect\SchemaExtractor\Dto\Product;
use HumanDirect\SchemaExtractor\Serializer\SchemaOrgBooleanDenormalizer;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Throwable;

/**
 * @template-implements SchemaParserInterface<SchemaTypeInterface>
 */
final readonly class SchemaParser implements SchemaParserInterface
{
    private Serializer $serializer;

    public function __construct()
    {
        $phpDocExtractor = new PhpDocExtractor();
        $reflectionExtractor = new ReflectionExtractor();

        $propertyTypeExtractor = new PropertyInfoExtractor(
            [$reflectionExtractor],
            [$phpDocExtractor, $reflectionExtractor],
            [$phpDocExtractor],
            [$reflectionExtractor],
            [$reflectionExtractor],
        );
        $classMetadataFactory = new ClassMetadataFactory(new AttributeLoader());

        $normalizer = new ObjectNormalizer(
            classMetadataFactory: $classMetadataFactory,
            nameConverter: new CamelCaseToSnakeCaseNameConverter(),
            propertyTypeExtractor: $propertyTypeExtractor,
        );

        $this->serializer = new Serializer(
            normalizers: [
                new SchemaOrgBooleanDenormalizer(),
                $normalizer,
                new ArrayDenormalizer(),
            ],
            encoders: [new JsonEncoder()],
        );
    }

    public function parse(array $data, string $type): ?SchemaTypeInterface
    {
        try {
            // Remove context and type before parsing
            unset($data['@context'], $data['@type']);

            $dtoClass = $this->getSchemaTypeClass($type);

            if ($dtoClass === null) {
                return null;
            }

            $context = [
                AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true,
            ];

            /** @var SchemaTypeInterface $data */
            $data = $this->serializer->denormalize(
                data: $data,
                type: $dtoClass,
                context: $context,
            );

            return $data;
        } catch (Throwable) {
            return null;
        }
    }

    private function getSchemaTypeClass(string $type): ?string
    {
        // Map Schema.org types to DTO classes
        return match ($type) {
            'Product' => Product::class,
            default => null,
        };
    }
}
