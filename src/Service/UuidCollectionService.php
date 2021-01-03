<?php declare(strict_types=1);

namespace App\Service;

use App\Cache\UuidCollectionWarmer;
use App\Exception\ApiExceptionTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;

class UuidCollectionService
{
    use ApiExceptionTrait;

    protected $collection;

    public function __construct(string $cacheDir)
    {
        $this->collection = include $cacheDir . '/uuid_collection.cache.php';
    }

    public function hasEntityPropertyUuidFormat(string $entityClass, string $propertyName): bool
    {
        return array_key_exists($entityClass, $this->collection) &&
            array_key_exists($propertyName, $this->collection[$entityClass]);
    }

    public function getEntityPropertyUuidFormat(string $entityClass, string $propertyName): array
    {
        return $this->collection[$entityClass][$propertyName] ?? [];
    }

    public function decodeEntityProperties(string $entityClass, array $properties)
    {
        foreach ($properties as $property => $value) {
            if (
                array_key_exists($entityClass, $this->collection) &&
                array_key_exists($property, $this->collection[$entityClass])
            ) {
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
        foreach ($properties as $property => $value) {
            if (
                array_key_exists($entityClass, $this->collection) &&
                array_key_exists($property, $this->collection[$entityClass])
            ) {
                $method = 'to' . ucfirst($this->collection[$entityClass][$property][UuidCollectionWarmer::KEY_ENCODE] ?? 'base32');
                $properties[$property] = $value->$method();
            }
        }

        return $properties;
    }
}
