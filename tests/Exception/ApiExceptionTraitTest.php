<?php declare(strict_types=1);

namespace App\Tests\Exception;

use App\Exception\ApiException;
use App\Exception\ApiExceptionTrait;
use PHPUnit\Framework\TestCase;

class ApiExceptionTraitTest extends TestCase
{
    /** @var ApiExceptionTrait|\PHPUnit\Framework\MockObject\MockObject */
    private $apiExceptionTrait;

    protected function setUp(): void
    {
        $this->apiExceptionTrait = $this->getMockForTrait(ApiExceptionTrait::class);
    }

    protected function tearDown(): void
    {
        $this->apiExceptionTrait = null;
    }

    public function testThrowApiException()
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('hello world');

        $this->apiExceptionTrait->throwApiException('hello world');
    }
}