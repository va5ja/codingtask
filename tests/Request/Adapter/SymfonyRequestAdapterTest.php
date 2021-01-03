<?php declare(strict_types=1);

namespace App\Tests\Request\Adapter;

use App\Request\Adapter\SymfonyRequestAdapter;
use App\Request\Request;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\RequestStack;

class SymfonyRequestAdapterTest extends TestCase
{
    private $symfonyRequestAdapter;

    protected function setUp(): void
    {
        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn(new SymfonyRequest(
            ['q' => 'abc'],
            [],
            [
                '_route' => 'route_name',
                '_route_params' => ['site' => 123],
                Request::ATTRIBUTE_ENTITY => 'App\Entity\Site',
                Request::ATTRIBUTE_PROVIDES => 'item',
                Request::ATTRIBUTE_IDENTIFIERS => [],
            ],
            [],
            [],
            [
                'SERVER_NAME' => 'localhost',
                'SERVER_PORT' => 80,
                'REQUEST_URI' => '/uri/hello?test=1',
                'QUERY_STRING' => 'test=1',
                'REQUEST_METHOD' => SymfonyRequest::METHOD_POST,
            ],
            'blabla'
        ));

        $this->symfonyRequestAdapter = new SymfonyRequestAdapter($requestStack);
    }

    protected function tearDown(): void
    {
        $this->symfonyRequestAdapter = null;
    }

    public function testGetUri()
    {
        $this->assertEquals('http://localhost/uri/hello?test=1', $this->symfonyRequestAdapter->getUri());
    }

    public function testGetMethod()
    {
        $this->assertEquals(SymfonyRequest::METHOD_POST, $this->symfonyRequestAdapter->getMethod());
    }

    public function testGetRouteName()
    {
        $this->assertEquals('route_name', $this->symfonyRequestAdapter->getRouteName());
    }

    public function testGetRouteParameters()
    {
        $this->assertEquals(['site' => 123], $this->symfonyRequestAdapter->getRouteParameters());
    }

    public function testGetQueryParameters()
    {
        $this->assertEquals(['q' => 'abc'], $this->symfonyRequestAdapter->getQueryParameters());
    }

    public function testGetContent()
    {
        $this->assertEquals('blabla', $this->symfonyRequestAdapter->getContent());
    }

    public function testGetEntityClassName()
    {
        $this->assertEquals('App\Entity\Site', $this->symfonyRequestAdapter->getEntityClassName());
    }

    public function testGetDataProviderType()
    {
        $this->assertEquals('item', $this->symfonyRequestAdapter->getDataProviderType());
    }

    public function testGetIdentifiers()
    {
        $this->assertEquals([], $this->symfonyRequestAdapter->getIdentifiers());
    }
}
