<?php declare(strict_types=1);

namespace App\Cache;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmer;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Symfony\Component\Routing\RouterInterface;

class RouteCollectionWarmer extends CacheWarmer implements CacheWarmerInterface
{
    /** @var RouterInterface */
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
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
        $routes = [];

        foreach ($this->router->getRouteCollection() as $name => $route) {
            $routes[$name] = [
                'methods' => $route->getMethods(),
                'defaults' => $route->getDefaults(),
                'requirements' => $route->getRequirements()
            ];
        }

        $this->writeCacheFile(
            $cacheDir . '/route_collection.cache.php',
            '<?php return ' . var_export($routes, true) . ';'
        );
    }
}