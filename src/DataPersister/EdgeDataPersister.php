<?php declare(strict_types=1);

namespace App\DataPersister;

use App\Entity\Edge;
use App\Entity\Graph;
use App\Request\Request;
use Symfony\Component\Uid\Uuid;

class EdgeDataPersister extends AbstractRepositoryPersister
{
    /**
     * @inheritdoc
     */
    public function persistData(array $data, Request $request): array
    {
        $entityManager = $this->entityManagerProvider->getManagerForClass($request->getEntityClassName());
        $graphIdentifiers = $request->getIdentifiers()[Graph::class] ?? [];

        if (in_array($request->getMethod(), [Request::METHOD_POST, Request::METHOD_PATCH])) {
            /** @var Edge $entity */
            foreach ($data as $entity) {
                $entity->setGraphId(Uuid::fromString($graphIdentifiers['id']));
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
        return $request->getEntityClassName() === Edge::class;
    }
}
