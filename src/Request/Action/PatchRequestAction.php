<?php declare(strict_types=1);

namespace App\Request\Action;

use App\Request\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class PatchRequestAction extends AbstractRequestAction
{
    protected const APPLICABLE_METHOD = Request::METHOD_PATCH;

    /**
     * @inheritdoc
     */
    public function process(Request $request)
    {
        $entityClassName = $request->getEntityClassName();
        $entityManager = $this->entityManager->getManagerForClass($entityClassName);

        $identifiers = $request->getEntityIdentifiers();
        $entity = $entityManager->find($entityClassName, $identifiers);

        $data = $this->serializer->deserialize($request->getContent(), $entityClassName, 'json', [
            AbstractNormalizer::OBJECT_TO_POPULATE => $entity,
            AbstractNormalizer::IGNORED_ATTRIBUTES => array_keys($identifiers),
        ]);

        $this->validate($data);

        $data = $this->dataPersister->saveData([$data], $request);

        $serializedData = $this->serializer->serialize($data[0] ?? [], 'json');

        return new JsonResponse($serializedData, Response::HTTP_OK, [], true);
    }
}
