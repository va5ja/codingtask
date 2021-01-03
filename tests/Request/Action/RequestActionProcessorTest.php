<?php declare(strict_types=1);

namespace App\Tests\Request\Action;

use App\Exception\InvalidArgumentException;
use App\Request\Action\RequestActionInterface;
use App\Request\Action\RequestActionProcessor;
use App\Request\Request;
use PHPUnit\Framework\TestCase;

class RequestActionProcessorTest extends TestCase
{
    /** @var RequestActionInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $strategy;

    /** @var Request|\PHPUnit\Framework\MockObject\MockObject */
    private $request;

    protected function setUp(): void
    {
        $this->strategy = $this->createMock(RequestActionInterface::class);
        $this->request = $this->createMock(Request::class);
    }

    protected function tearDown(): void
    {
        $this->strategy = null;
        $this->request = null;
    }

    public function testProcess()
    {
        $this->strategy->method('isApplicable')->willReturn(true);
        $this->strategy->expects($this->once())->method('process')->willReturn('success');

        $requestActionProcessor = new RequestActionProcessor([$this->strategy]);

        $this->assertEquals('success', $requestActionProcessor->process($this->request));
    }

    public function testProcessUnsupportedStrategy()
    {
        $this->strategy->method('isApplicable')->willReturn(false);
        $this->strategy->expects($this->never())->method('process')->willReturn('success');

        $this->expectException(InvalidArgumentException::class);

        $requestActionProcessor = new RequestActionProcessor([$this->strategy]);
        $requestActionProcessor->process($this->request);
    }

    public function testProcessNoStrategies()
    {
        $this->expectException(InvalidArgumentException::class);

        $requestActionProcessor = new RequestActionProcessor([]);
        $requestActionProcessor->process($this->request);
    }
}
