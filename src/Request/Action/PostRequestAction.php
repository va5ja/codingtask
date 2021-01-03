<?php declare(strict_types=1);

namespace App\Request\Action;

use App\Request\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class PostRequestAction extends AbstractRequestAction
{
    use AbstractRequestActionTrait;

    protected const APPLICABLE_METHOD = Request::METHOD_POST;

    /**
     * @inheritdoc
     */
    public function process(Request $request)
    {
        $multiple = true;
        $content = $request->getContent();

        // support multiple entities
        if (preg_match('/^\s*?{/i', $content)) {
            $multiple = false;
            $content = '[' . $content . ']';
        }

        $entityClassName = $request->getEntityClassName();
        $data = $this->serializer->deserialize($content, $entityClassName . '[]', 'json');

        $this->validate($data);

        $this->dataPersister->saveData($data, $request);

        $serializedData = $this->serializer->serialize($multiple ? $data : $data[0], 'json');

        $headers = $this->generateLocationHeader($data, $request);

        return new JsonResponse($serializedData, Response::HTTP_CREATED, $headers, true);
    }
}
