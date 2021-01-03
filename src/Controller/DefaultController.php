<?php declare(strict_types=1);

namespace App\Controller;

use App\EntityManager\EntityManagerProvider;
use App\Exception\ApiExceptionTrait;
use App\Request\Action\RequestActionProcessor;
use App\Request\Request;
use App\Security\AbstractEntityVoter;
use App\Security\UserRoles;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends AbstractController
{
    use ApiExceptionTrait;

    public function process(
        Request $request,
        EntityManagerProvider $entityManager,
        RequestActionProcessor $requestActionProcessor
    ): Response {
        $this->denyAccessUnlessGranted(UserRoles::ROLE_USER, null, 'Access denied. Insufficient user role.');

        foreach ($request->getIdentifiers() as $entityClassName => $identifiers) {
            if (!$identifiers) {
                continue;
            }

            if (!$entity = $entityManager->getManagerForClass($entityClassName)->find($entityClassName, $identifiers)) {
                $this->throwApiException('The requested resource does not exist.', Response::HTTP_NOT_FOUND);
            }

            $this->denyAccessUnlessGranted(
                $request->getMethod() === Request::METHOD_GET ?
                    AbstractEntityVoter::ATTRIBUTE_READ :
                    AbstractEntityVoter::ATTRIBUTE_WRITE,
                $entity,
                'Access denied to the requested resource.'
            );
        }

        return $requestActionProcessor->process($request);
    }
}
