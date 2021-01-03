<?php declare(strict_types=1);

namespace App\DataPersister;

use App\Request\Request;

class SimpleDataPersister extends AbstractRepositoryPersister
{
    /**
     * @inheritdoc
     */
    public function persistData(array $data, Request $request): array
    {
        $entityManager = $this->entityManagerProvider->getManagerForClass($request->getEntityClassName());

        if ($request->getMethod() === Request::METHOD_POST) {
            foreach ($data as $entity) {
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
        return true;
    }

    public static function getDefaultPriority(): int
    {
        return -1;
    }
}
