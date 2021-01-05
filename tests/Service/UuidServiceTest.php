<?php declare(strict_types=1);

namespace App\Tests\Service;

use App\EntityManager\Metadata\MetadataProvider;
use App\Service\UuidService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class UuidServiceTest extends TestCase
{
    private $metadataProvider;

    protected function setUp(): void
    {
        $this->metadataProvider = $this->createMock(MetadataProvider::class);
        $this->metadataProvider->method('getClassMetadata')->with('App\\Entity\\Graph')->willReturn([
            'identifiers' => [
                'id' => [
                    'type' => 'uuid',
                    'version' => 4,
                    'encode' => 'base32'
                ]
            ]
        ]);
    }

    protected function tearDown(): void
    {
        $this->metadataProvider = null;
    }

    public function testDecodeEntityProperties()
    {
        $uuidCollectionService = new UuidService($this->metadataProvider);
        $methods = $uuidCollectionService->decodeEntityProperties(
            'App\\Entity\\Graph',
            ['id' => '6CD0QG3X428XSSZHHPS4QETYKH']
        );

        $this->assertEquals(['id' => 'cc682f01-f482-4773-9fc6-36c92eed7a71'], $methods);
    }

    public function testEncodeEntityProperties()
    {
        $uuidCollectionService = new UuidService($this->metadataProvider);
        $methods = $uuidCollectionService->encodeEntityProperties(
            'App\\Entity\\Graph',
            ['id' => Uuid::fromString('cc682f01-f482-4773-9fc6-36c92eed7a71')]
        );

        $this->assertEquals(['id' => '6CD0QG3X428XSSZHHPS4QETYKH'], $methods);
    }
}
