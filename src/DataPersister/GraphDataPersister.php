<?php declare(strict_types=1);

namespace App\DataPersister;

use App\Entity\Graph;
use App\Request\Request;

class GraphDataPersister extends AbstractRepositoryPersister
{
    /**
     * @inheritdoc
     */
    public function persistData(array $data, Request $request): array
    {
        $entityManager = $this->entityManagerProvider->getManagerForClass($request->getEntityClassName());

        if (in_array($request->getMethod(), [Request::METHOD_POST, Request::METHOD_PATCH])) {
            /** @var Graph $entity */
            foreach ($data as $entity) {
                $entity->setUser($this->getUser());
                $entityManager->persist($entity);
            }
        }

        if ($request->getMethod() === Request::METHOD_DELETE) {
            foreach ($data as $entity) {
                $entityManager->remove($entity);
            }
        }

        $entityManager->flush();

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function isApplicable(array $data, Request $request): bool
    {
        return $request->getEntityClassName() === Graph::class;
    }
}
