<?php declare(strict_types=1);

namespace App\Request\Action;

use App\Request\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class DeleteRequestAction extends AbstractRequestAction
{
    protected const APPLICABLE_METHOD = Request::METHOD_DELETE;

    /**
     * @inheritdoc
     */
    public function process(Request $request)
    {
        $entityClassName = $request->getEntityClassName();
        $entityManager = $this->entityManager->getManagerForClass($entityClassName);

        $entity = $entityManager->find($entityClassName, $request->getEntityIdentifiers());

        $serializedEntity = $this->serializer->serialize($entity, 'json');

        $this->dataPersister->saveData([$entity], $request);

        return new JsonResponse($serializedEntity, Response::HTTP_OK, [], true);
    }
}
