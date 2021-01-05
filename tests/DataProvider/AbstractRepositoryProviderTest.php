<?php declare(strict_types=1);

namespace App\Tests\DataProvider;

use App\DataProvider\AbstractRepositoryProvider;
use App\EntityManager\EntityManagerInterface;
use App\EntityManager\EntityManagerProvider;
use App\Repository\RepositoryInterface;
use App\Request\Request;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Security;

class AbstractRepositoryProviderTest extends TestCase
{
    /** @var EntityManagerProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $entityManagerProvider;

    /** @var EntityManagerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $entityManager;

    /** @var \PHPUnit\Framework\MockObject\MockObject|Security */
    private $security;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $anonymousDoctrineProvider;

    /** @var LoggerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $logger;

    protected function setUp(): void
    {
        $this->entityManagerProvider = $this->createMock(EntityManagerProvider::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->security = $this->createMock(Security::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->anonymousDoctrineProvider = new class(
            $this->entityManagerProvider,
            $this->security,
            $this->logger
        ) extends AbstractRepositoryProvider {
            public function isApplicable(Request $request): bool
            {
            }

            public function provideData(Request $request)
            {
            }
        };
    }

    protected function tearDown(): void
    {
        $this->entityManagerProvider = null;
        $this->entityManager = null;
        $this->security = null;
        $this->anonymousDoctrineProvider = null;
    }

    public function testGetEntityRepository()
    {
        $request = $this->createMock(Request::class);
        $request->method('getEntityClassName')->willReturn('className');

        $objectRepository = $this->createMock(RepositoryInterface::class);

        $this->entityManager->expects($this->once())->method('getRepository')->with('className')->willReturn($objectRepository);
        $this->entityManagerProvider->expects($this->once())->method('getManagerForClass')->with('className')->willReturn($this->entityManager);

        $this->anonymousDoctrineProvider->getEntityRepository($request);
    }

    public function testGetUser()
    {
        $this->security->expects($this->once())->method('getUser');

        $this->anonymousDoctrineProvider->getUser();
    }
}
