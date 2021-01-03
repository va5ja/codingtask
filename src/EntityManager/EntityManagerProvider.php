<?php declare(strict_types=1);

namespace App\EntityManager;

use App\Exception\InvalidArgumentException;

class EntityManagerProvider
{
    /** @var EntityManagerInterface[] */
    private $entityManagers;

    public function __construct(iterable $entityManagers)
    {
        $this->entityManagers = $entityManagers;
    }

    public function getManagerForClass(string $entityClassName): EntityManagerInterface
    {
        foreach ($this->entityManagers as $entityManager) {
            if ($entityManager->supports($entityClassName)) {
                return $entityManager;
            }
        }

        throw new InvalidArgumentException("Entity \"$entityClassName\" does not belong to any entity manager.");
    }
}
