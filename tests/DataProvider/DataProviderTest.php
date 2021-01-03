<?php declare(strict_types=1);

namespace App\Tests\DataProvider;

use App\DataProvider\DataProvider;
use App\DataProvider\DataProviderInterface;
use App\Exception\InvalidArgumentException;
use App\Request\Request;
use PHPUnit\Framework\TestCase;

class DataProviderTest extends TestCase
{
    /** @var RequestActionInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $strategy;

    /** @var Request|\PHPUnit\Framework\MockObject\MockObject */
    private $request;

    protected function setUp(): void
    {
        $this->strategy = $this->createMock(DataProviderInterface::class);
        $this->request = $this->createMock(Request::class);
    }

    protected function tearDown(): void
    {
        $this->strategy = null;
        $this->request = null;
    }

    public function testGetData()
    {
        $this->strategy->method('isApplicable')->willReturn(true);
        $this->strategy->expects($this->once())->method('provideData')->willReturn('success');

        $requestActionContext = new DataProvider([$this->strategy]);

        $this->assertEquals(
            'success',
            $requestActionContext->getData($this->request)
        );
    }

    public function testProcessUnsupportedStrategy()
    {
        $this->strategy->method('isApplicable')->willReturn(false);
        $this->strategy->expects($this->never())->method('provideData')->willReturn('success');

        $this->expectException(InvalidArgumentException::class);

        $requestActionContext = new DataProvider([$this->strategy]);
        $requestActionContext->getData($this->request);
    }

    public function testProcessNoStrategies()
    {
        $this->expectException(InvalidArgumentException::class);

        $requestActionContext = new DataProvider([]);
        $requestActionContext->getData($this->request);
    }
}
