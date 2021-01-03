<?php declare(strict_types=1);

namespace App\Tests\Exception;

use App\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class InvalidArgumentExceptionTest extends TestCase
{
    public function testConstruct()
    {
        $apiException = new InvalidArgumentException('hello world', 128);

        $this->assertEquals('hello world', $apiException->getMessage());
        $this->assertEquals(128, $apiException->getCode());
    }
}