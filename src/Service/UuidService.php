<?php declare(strict_types=1);

namespace App\Service;

use App\EntityManager\Metadata\Extractor\IdExtractor;
use App\EntityManager\Metadata\MetadataProvider;
use App\Exception\ApiExceptionTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;

class UuidService
{
    use ApiExceptionTrait;

    /** @var MetadataProvider */
    protected $metadataProvider;

    public function __construct(MetadataProvider $metadataProvider)
    {
        $this->metadataProvider = $metadataProvider;
    }

    public function decodeEntityProperties(string $entityClass, array $properties)
    {
        $metadata = $this->metadataProvider->getClassMetadata($entityClass);
        $identifiers = $metadata[IdExtractor::KEY_EXTRACTION] ?? [];

        foreach ($properties as $property => $value) {
            if (array_key_exists($property, $identifiers) && ($identifiers[$property]['type'] ?? '') === 'uuid') {
                try {
                    $properties[$property] = (string)Uuid::fromString($value);
                } catch (\InvalidArgumentException $e) {
                    $this->throwApiException('Invalid or malformed resource identifier.', Response::HTTP_NOT_FOUND);
                }
            }
        }

        return $properties;
    }

    public function encodeEntityProperties(string $entityClass, array $properties)
    {
        $metadata = $this->metadataProvider->getClassMetadata($entityClass);
        $identifiers = $metadata[IdExtractor::KEY_EXTRACTION] ?? [];

        foreach ($properties as $property => $value) {
            if (array_key_exists($property, $identifiers) && ($identifiers[$property]['type'] ?? '') === 'uuid') {
                $method = 'to' . ucfirst($identifiers[$property][IdExtractor::KEY_ENCODE] ?? 'base32');
                $properties[$property] = $value->$method();
            }
        }

        return $properties;
    }
}
