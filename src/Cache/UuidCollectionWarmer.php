<?php declare(strict_types=1);

namespace App\Cache;

use App\Annotation\Uuid;
use App\Request\Request;
use Doctrine\Common\Annotations\Reader;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmer;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Symfony\Component\Routing\RouterInterface;

class UuidCollectionWarmer extends CacheWarmer implements CacheWarmerInterface
{
    public const KEY_VERSION = 'version';
    public const KEY_ENCODE = 'encode';

    /** @var RouterInterface */
    private $router;

    /** @var Reader */
    private $reader;

    public function __construct(RouterInterface $router, Reader $reader)
    {
        $this->router = $router;
        $this->reader = $reader;
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
        $collection = [];

        foreach ($this->router->getRouteCollection() as $name => $route) {
            $defaults = $route->getDefaults();

            if (array_key_exists(Request::ATTRIBUTE_ENTITY, $defaults)) {
                foreach ((new \ReflectionClass($defaults[Request::ATTRIBUTE_ENTITY]))->getProperties() as $reflectionProperty) {
                    if (null !== $uuidAnnotation = $this->reader->getPropertyAnnotation($reflectionProperty, Uuid::class)) {
                        $collection[$defaults[Request::ATTRIBUTE_ENTITY]][$reflectionProperty->getName()] = [
                            self::KEY_VERSION => $uuidAnnotation->version,
                            self::KEY_ENCODE => $uuidAnnotation->encode
                        ];
                    }
                }
            }
        }

        $this->writeCacheFile(
            $cacheDir . '/uuid_collection.cache.php',
            '<?php return ' . var_export($collection, true) . ';'
        );
    }
}
