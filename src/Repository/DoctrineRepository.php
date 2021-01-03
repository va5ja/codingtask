<?php declare(strict_types=1);

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;

class DoctrineRepository implements RepositoryInterface
{
    /** @var ServiceEntityRepositoryInterface */
    private $entityRepository;

    public function __construct(ServiceEntityRepositoryInterface $entityRepository)
    {
        $this->entityRepository = $entityRepository;
    }

    /**
     * @inheritdoc
     */
    public function find($id): ?object
    {
        return $this->entityRepository->find($id);
    }

    /**
     * @inheritdoc
     */
    public function findAll(): array
    {
        return $this->entityRepository->findAll();
    }

    /**
     * @inheritdoc
     */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        return $this->entityRepository->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * @inheritdoc
     */
    public function findOneBy(array $criteria): ?object
    {
        return $this->entityRepository->findOneBy($criteria);
    }

    /**
     * @inheritdoc
     */
    public function count(array $criteria): int
    {
        return $this->entityRepository->count($criteria);
    }

    /**
     * @inheritdoc
     */
    public function getClassName(): string
    {
        return $this->entityRepository->getClassName();
    }

    public function __call(string $name, array $arguments)
    {
        $this->entityRepository->$name(...$arguments);
    }
}
