<?php declare(strict_types=1);

namespace App\Service;

class RouteCollectionService
{
    protected $routes;

    public function __construct(string $cacheDir)
    {
        $this->routes = include $cacheDir . '/route_collection.cache.php';
    }

    public function getRouteMethods(string $routeName)
    {
        return $this->routes[$routeName]['methods'] ?? [];
    }

    public function getRouteDefaults(string $routeName)
    {
        return $this->routes[$routeName]['defaults'] ?? [];
    }

    public function getRouteRequirements(string $routeName)
    {
        return $this->routes[$routeName]['requirements'] ?? [];
    }
}
