<?php

declare(strict_types=1);

namespace HumanDirect\SchemaExtractor\Serializer;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class SchemaOrgBooleanDenormalizer implements DenormalizerInterface
{
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        if (!is_string($data)) {
            return (bool) $data;
        }

        return match (str_replace(['http:', 'https:'], '', $data)) {
            '//schema.org/True', '//schema.org/true' => true,
            '//schema.org/False', '//schema.org/false' => false,
            default => (bool) $data,
        };
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        if (!($type === 'bool' || $type === 'boolean')) {
            return false;
        }

        if (!is_string($data)) {
            return false;
        }

        $normalized = str_replace(['http:', 'https:'], '', $data);

        return str_starts_with($normalized, '//schema.org/');
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            'bool' => true,
            'boolean' => true,
        ];
    }
}
