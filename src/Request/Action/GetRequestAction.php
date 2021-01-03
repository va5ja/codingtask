<?php declare(strict_types=1);

namespace App\Request\Action;

use App\Request\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class GetRequestAction extends AbstractRequestAction
{
    protected const APPLICABLE_METHOD = Request::METHOD_GET;

    /**
     * @inheritdoc
     */
    public function process(Request $request): Response
    {
        $data = $this->dataProvider->getData($request);

        $serializedData = $this->serializer->serialize($data, 'json');

        return new JsonResponse($serializedData, Response::HTTP_OK, [], true);
    }
}
