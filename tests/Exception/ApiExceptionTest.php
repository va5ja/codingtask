<?php declare(strict_types=1);

namespace App\Tests\Exception;

use App\Exception\ApiException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class ApiExceptionTest extends TestCase
{
    public function testConstruct()
    {
        $apiException = new ApiException(
            Response::HTTP_OK,
            'hello world',
            null,
            128,
            ['x-foo' => 'bar']
        );

        $this->assertEquals(Response::HTTP_OK, $apiException->getStatusCode());
        $this->assertEquals('hello world', $apiException->getMessage());
        $this->assertNull($apiException->getPrevious());
        $this->assertEquals(128, $apiException->getCode());
        $this->assertEquals(['x-foo' => 'bar'], $apiException->getHeaders());
    }
}