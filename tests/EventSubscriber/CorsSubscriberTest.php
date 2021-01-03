<?php declare(strict_types=1);

namespace App\Tests\EventSubscriber;

use App\EventSubscriber\CorsSubscriber;
use App\Service\RouteCollectionService;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBag;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class CorsSubscriberTest extends TestCase
{
    /** @var RouteCollectionService|\PHPUnit\Framework\MockObject\MockObject */
    private $routeCollectionService;

    /** @var CorsSubscriber */
    private $corsSubscriber;

    protected function setUp(): void
    {
        $this->routeCollectionService = $this->createMock(RouteCollectionService::class);
        $this->corsSubscriber = new CorsSubscriber(
            $this->routeCollectionService,
            new ContainerBag(new Container(new ParameterBag(['app.cors_origin' => '/^.*$/i'])))
        );
    }

    protected function tearDown(): void
    {
        $this->routeCollectionService = null;
        $this->corsSubscriber = null;
    }

    public function testGetSubscribedEvents()
    {
        $this->assertNotEmpty(CorsSubscriber::getSubscribedEvents());
    }

    public function testOnKernelRequestNotMasterRequest()
    {
        $requestEvent = $this->createMock(RequestEvent::class);
        $requestEvent->method('isMasterRequest')->willReturn(false);
        $requestEvent->expects($this->never())->method('getRequest');

        $this->assertNull($this->corsSubscriber->onKernelRequest($requestEvent));
    }

    public function testOnKernelRequestPreflightRequest()
    {
        $this->routeCollectionService->method('getRouteMethods')->willReturn([
            REQUEST::METHOD_GET,
            REQUEST::METHOD_OPTIONS
        ]);

        $request = new Request(
            [],
            [],
            [
                '_route' => 'route_name',
            ],
            [],
            [],
            [
                'REQUEST_METHOD' => Request::METHOD_OPTIONS,
                'HTTP_ORIGIN' => 'pup',
                'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => Request::METHOD_OPTIONS,
                'HTTP_ACCESS_CONTROL_REQUEST_HEADERS' => 'headers',
            ]
        );

        $requestEvent = $this->createMock(RequestEvent::class);
        $requestEvent->method('isMasterRequest')->willReturn(true);
        $requestEvent->method('getRequest')->willReturn($request);
        $requestEvent->expects($this->once())->method('setResponse')->willReturnCallback(function (
            Response $response
        ) {
            Assert::assertEquals(Response::HTTP_OK, $response->getStatuscode());
            Assert::assertEquals('GET, OPTIONS', $response->headers->get('Access-Control-Allow-Methods'));
            Assert::assertEquals(86400, $response->headers->get('Access-Control-Max-Age'));
            Assert::assertEquals('GET, OPTIONS', $response->headers->get('Allow'));
        });

        $this->assertNull($this->corsSubscriber->onKernelRequest($requestEvent));
    }
}
