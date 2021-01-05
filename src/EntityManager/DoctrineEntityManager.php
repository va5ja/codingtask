<?php declare(strict_types=1);

namespace App\EntityManager;

use App\Repository\DoctrineRepository;
use App\Repository\RepositoryInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;

class DoctrineEntityManager implements EntityManagerInterface
{
    /** @var ManagerRegistry */
    private $registry;

    private $classManagerCache = [];

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @inheritdoc
     */
    public function supports(string $entityClassName): bool
    {
        return $this->getManagerForClass($entityClassName) !== null;
    }

    /**
     * @inheritdoc
     */
    public function find(string $className, $id): ?object
    {
        return $this->getManagerForClass($className)->find($className, $id);
    }

    /**
     * @inheritdoc
     */
    public function persist(object $object): void
    {
        $this->getManagerForClass(get_class($object))->persist($object);
    }

    /**
     * @inheritdoc
     */
    public function remove(object $object): void
    {
        $this->getManagerForClass(get_class($object))->remove($object);
    }

    /**
     * @inheritdoc
     */
    public function clear(?string $objectName = null): void
    {
        foreach ($this->registry->getManagers() as $manager) {
            $manager->clear($objectName);
        }
    }

    /**
     * @inheritdoc
     */
    public function refresh(object $object): void
    {
        $this->getManagerForClass(get_class($object))->refresh($object);
    }

    /**
     * @inheritdoc
     */
    public function flush(): void
    {
        foreach ($this->registry->getManagers() as $manager) {
            $manager->flush();
        }
    }

    /**
     * @inheritdoc
     */
    public function getRepository(string $className): RepositoryInterface
    {
        return new DoctrineRepository($this->getManagerForClass($className)->getRepository($className));
    }

    /**
     * @inheritdoc
     */
    public function getClassMetadata(string $className): ClassMetadata
    {
        return $this->getManagerForClass($className)->getClassMetadata($className);
    }

    /**
     * @inheritdoc
     */
    public function contains(object $object): bool
    {
        return $this->getManagerForClass(get_class($object))->contains($object);
    }

    private function getManagerForClass(string $class): ?ObjectManager
    {
        if (array_key_exists($class, $this->classManagerCache)) {
            return $this->classManagerCache[$class];
        }

        if (($manager = $this->registry->getManagerForClass($class)) !== null) {
            $this->classManagerCache[$class] = $manager;
        }

        return $manager;
    }
}
