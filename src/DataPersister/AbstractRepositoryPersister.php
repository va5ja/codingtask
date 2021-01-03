<?php declare(strict_types=1);

namespace App\DataPersister;

use App\EntityManager\EntityManagerProvider;
use App\Repository\RepositoryInterface;
use App\Request\Request;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class AbstractRepositoryPersister implements DataPersisterInterface
{
    /** @var EntityManagerProvider */
    protected $entityManagerProvider;

    /** @var Security */
    protected $security;

    /** @var LoggerInterface */
    protected $logger;

    public function __construct(
        EntityManagerProvider $entityManagerProvider,
        Security $security,
        LoggerInterface $logger
    ) {
        $this->entityManagerProvider = $entityManagerProvider;
        $this->security = $security;
        $this->logger = $logger;
    }

    public function getEntityRepository(Request $request): RepositoryInterface
    {
        $entityClassName = $request->getEntityClassName();

        return $this->entityManagerProvider->getManagerForClass($entityClassName)->getRepository($entityClassName);
    }

    public function getUser(): ?UserInterface
    {
        return $this->security->getUser();
    }
}
