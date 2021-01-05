<?php declare(strict_types=1);

namespace App\Cache;

use App\EntityManager\Metadata\Extractor\MetadataExtractor;
use App\Request\Request;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmer;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Symfony\Component\Routing\RouterInterface;

class EntityMetadataWarmer extends CacheWarmer implements CacheWarmerInterface
{
    /** @var RouterInterface */
    private $router;

    /** @var MetadataExtractor */
    private $metadataExtractor;

    public function __construct(RouterInterface $router, MetadataExtractor $metadataExtractor)
    {
        $this->router = $router;
        $this->metadataExtractor = $metadataExtractor;
    }

    /**
     * {@inheritdoc}
     */
    public function isOptional()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp($cacheDir)
    {
        $metadata = [];

        foreach ($this->router->getRouteCollection() as $name => $route) {
            $defaults = $route->getDefaults();

            if (array_key_exists(Request::ATTRIBUTE_ENTITY, $defaults)) {
                $entityClassName = $defaults[Request::ATTRIBUTE_ENTITY];
                $metadata[$entityClassName] = $this->metadataExtractor->extractMetadataForClass($entityClassName);
            }
        }

        $this->writeCacheFile(
            $cacheDir . '/entity_metadata.cache.php',
            '<?php return ' . var_export($metadata, true) . ';'
        );
    }
}
