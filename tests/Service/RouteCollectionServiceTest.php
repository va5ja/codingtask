<?php declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\RouteCollectionService;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class RouteCollectionServiceTest extends TestCase
{
    private $root;

    protected function setUp(): void
    {
        $this->root = vfsStream::setup('root', 644, [
            'cache' => [
                'route_collection.cache.php' => "<?php return [
                    'route' => [
                        'methods' => ['GET'],
                        'defaults' => ['a' => 'b'],
                        'requirements' => ['c' => 'd']
                    ]
                ];"
            ],
        ]);
    }

    protected function tearDown(): void
    {
        $this->root = null;
    }

    public function testGetRouteMethods()
    {
        $routeCollectionService = new RouteCollectionService($this->root->url() . '/cache');
        $methods = $routeCollectionService->getRouteMethods('route');

        $this->assertEquals(['GET'], $methods);
    }

    public function testGetRouteDefaults()
    {
        $routeCollectionService = new RouteCollectionService($this->root->url() . '/cache');
        $defaults = $routeCollectionService->getRouteDefaults('route');

        $this->assertEquals(['a' => 'b'], $defaults);
    }

    public function testGetRouteRequirements()
    {
        $routeCollectionService = new RouteCollectionService($this->root->url() . '/cache');
        $requirements = $routeCollectionService->getRouteRequirements('route');

        $this->assertEquals(['c' => 'd'], $requirements);
    }
}
