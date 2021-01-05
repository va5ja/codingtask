<?php declare(strict_types=1);

namespace App\Tests\Request;

use App\Request\Adapter\RequestAdapterInterface;
use App\Request\Request;
use App\Service\UuidService;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    private $adapter;

    private $uuidCollectionService;

    private $request;

    protected function setUp(): void
    {
        $this->adapter = $this->createMock(RequestAdapterInterface::class);
        $this->adapter->method('getUri')->willReturn('/uri/hello?test=1');
        $this->adapter->method('getMethod')->willReturn(Request::METHOD_POST);
        $this->adapter->method('getRouteName')->willReturn('route');
        $this->adapter->method('getRouteParameters')->willReturn([
            'page' => 'hello',
        ]);
        $this->adapter->method('getQueryParameters')->willReturn(['site' => '1']);
        $this->adapter->method('getContent')->willReturn('[{"a": "b"}]');
        $this->adapter->method('getEntityClassName')->willReturn('App\Entity\Test');
        $this->adapter->method('getDataProviderType')->willReturn(Request::PROVIDER_ITEM);

        $this->uuidCollectionService = $this->createMock(UuidService::class);
        $this->uuidCollectionService->method('decodeEntityProperties')->will($this->returnArgument(1));

        $this->request = new Request($this->adapter, $this->uuidCollectionService);
    }

    protected function tearDown(): void
    {
        $this->adapter = null;
        $this->uuidCollectionService = null;
        $this->request = null;
    }

    public function testGetUri()
    {
        $this->assertEquals('/uri/hello?test=1', $this->request->getUri());
    }

    public function testGetMethod()
    {
        $this->assertEquals(Request::METHOD_POST, $this->request->getMethod());
    }

    public function testGetRouteName()
    {
        $this->assertEquals('route', $this->request->getRouteName());
    }

    public function testGetRouteParameters()
    {
        $this->assertEquals(['page' => 'hello'], $this->request->getRouteParameters());
    }

    public function testGetRouteParameter()
    {
        $this->assertEquals('hello', $this->request->getRouteParameter('page'));
    }

    public function testGetRouteParameterDefault()
    {
        $this->assertEquals('bla', $this->request->getRouteParameter('nope', 'bla'));
    }

    public function testGetQueryParameters()
    {
        $this->assertEquals(['site' => '1'], $this->request->getQueryParameters());
    }

    public function testGetQueryParameter()
    {
        $this->assertEquals('1', $this->request->getQueryParameter('site'));
    }

    public function testGetQueryParameterDefault()
    {
        $this->assertEquals('bla', $this->request->getQueryParameter('nope', 'bla'));
    }

    public function testGetContent()
    {
        $this->assertEquals('[{"a": "b"}]', $this->request->getContent());
    }

    public function testGetEntityClassName()
    {
        $this->assertEquals('App\Entity\Test', $this->request->getEntityClassName());
    }

    public function testGetDataProviderType()
    {
        $this->assertEquals(Request::PROVIDER_ITEM, $this->request->getDataProviderType());
    }

    public function testGetIdentifiers()
    {
        $this->assertEquals(['App\Entity\Test' => ['page' => 'hello']], $this->request->getIdentifiers());
    }

    public function testGetEntityIdentifiers()
    {
        $this->assertEquals(['page' => 'hello'], $this->request->getEntityIdentifiers());
    }
}
