<?php declare(strict_types=1);

namespace App\EntityManager\Metadata;

class MetadataProvider
{
    protected $metadata;

    public function __construct(string $cacheDir)
    {
        $this->metadata = include $cacheDir . '/entity_metadata.cache.php';
    }

    public function getClassMetadata(string $className): array
    {
        return $this->metadata[$className];
    }
}
