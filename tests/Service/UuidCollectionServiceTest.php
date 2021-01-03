<?php declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\UuidCollectionService;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class UuidCollectionServiceTest extends TestCase
{
    private $root;

    protected function setUp(): void
    {
        $this->root = vfsStream::setup('root', 644, [
            'cache' => [
                'uuid_collection.cache.php' => "<?php return [
                    'App\\Entity\\Graph' => [
                        'id' => [
                            'version' => 4,
                            'encode' => 'base32',
                        ]
                    ]
                ];"
            ],
        ]);
    }

    protected function tearDown(): void
    {
        $this->root = null;
    }

    public function testHasEntityPropertyUuidFormat()
    {
        $uuidCollectionService = new UuidCollectionService($this->root->url() . '/cache');
        $hasUuidFormat = $uuidCollectionService->hasEntityPropertyUuidFormat('App\\Entity\\Graph', 'id');

        $this->assertTrue($hasUuidFormat);
    }

    public function testGetEntityPropertyUuidFormat()
    {
        $uuidCollectionService = new UuidCollectionService($this->root->url() . '/cache');
        $methods = $uuidCollectionService->getEntityPropertyUuidFormat('App\\Entity\\Graph', 'id');

        $this->assertEquals(['version' => 4, 'encode' => 'base32'], $methods);
    }

    public function testDecodeEntityProperties()
    {
        $uuidCollectionService = new UuidCollectionService($this->root->url() . '/cache');
        $methods = $uuidCollectionService->decodeEntityProperties('App\\Entity\\Graph',
            ['id' => '6CD0QG3X428XSSZHHPS4QETYKH']);

        $this->assertEquals(['id' => 'cc682f01-f482-4773-9fc6-36c92eed7a71'], $methods);
    }

    public function testEncodeEntityProperties()
    {
        $uuidCollectionService = new UuidCollectionService($this->root->url() . '/cache');
        $methods = $uuidCollectionService->encodeEntityProperties('App\\Entity\\Graph',
            ['id' => Uuid::fromString('cc682f01-f482-4773-9fc6-36c92eed7a71')]);

        $this->assertEquals(['id' => '6CD0QG3X428XSSZHHPS4QETYKH'], $methods);
    }
}
