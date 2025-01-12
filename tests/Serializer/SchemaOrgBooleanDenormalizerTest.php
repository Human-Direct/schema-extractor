<?php

declare(strict_types=1);

namespace Serializer;

use HumanDirect\SchemaExtractor\Serializer\SchemaOrgBooleanDenormalizer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(SchemaOrgBooleanDenormalizer::class)]
final class SchemaOrgBooleanDenormalizerTest extends TestCase
{
    private SchemaOrgBooleanDenormalizer $denormalizer;

    #[Test]
    #[DataProvider('provideBooleanValues')]
    public function itShouldDenormalizeBooleanValues(mixed $input, bool $expected): void
    {
        $result = $this->denormalizer->denormalize($input, 'bool');

        self::assertSame($expected, $result);
    }

    /**
     * @return array<string, array{mixed, bool}>
     */
    public static function provideBooleanValues(): array
    {
        return [
            'schema.org true (https)' => ['https://schema.org/True', true],
            'schema.org false (https)' => ['https://schema.org/False', false],
            'schema.org true (http)' => ['http://schema.org/True', true],
            'schema.org false (http)' => ['http://schema.org/False', false],
            'schema.org lowercase true' => ['https://schema.org/true', true],
            'schema.org lowercase false' => ['https://schema.org/false', false],
        ];
    }

    #[Test]
    public function itShouldSupportBooleanTypes(): void
    {
        self::assertTrue($this->denormalizer->supportsDenormalization('http://schema.org/True', 'bool'));
        self::assertTrue($this->denormalizer->supportsDenormalization('https://schema.org/False', 'bool'));
        self::assertFalse($this->denormalizer->supportsDenormalization('true', 'bool'));
        self::assertFalse($this->denormalizer->supportsDenormalization('false', 'bool'));
        self::assertFalse($this->denormalizer->supportsDenormalization(true, 'bool'));
        self::assertFalse($this->denormalizer->supportsDenormalization(false, 'bool'));
        self::assertFalse($this->denormalizer->supportsDenormalization(1, 'bool'));
        self::assertFalse($this->denormalizer->supportsDenormalization(0, 'bool'));
        self::assertFalse($this->denormalizer->supportsDenormalization('any', 'string'));
    }

    protected function setUp(): void
    {
        $this->denormalizer = new SchemaOrgBooleanDenormalizer();
    }
}
