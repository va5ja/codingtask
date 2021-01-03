<?php declare(strict_types=1);

namespace App\EventSubscriber;

use App\Service\RouteCollectionService;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class CorsSubscriber implements EventSubscriberInterface
{
    /** @var RouteCollectionService */
    private $routeCollectionService;

    /** @var ContainerBagInterface */
    private $params;

    public function __construct(RouteCollectionService $routeCollectionService, ContainerBagInterface $params)
    {
        $this->routeCollectionService = $routeCollectionService;
        $this->params = $params;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
            KernelEvents::RESPONSE => 'onKernelResponse'
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();
        $method = $request->getRealMethod();

        if ($method === Request::METHOD_OPTIONS) {
            $routeName = $request->attributes->get('_route');
            $routeMethods = $this->routeCollectionService->getRouteMethods($routeName);

            $response = new JsonResponse();
            $response->setStatusCode(Response::HTTP_OK);

            if (
                $request->headers->get('Origin') !== null &&
                $request->headers->get('Access-Control-Request-Method') !== null &&
                $request->headers->get('Access-Control-Request-Headers') !== null
            ) {
                $response->headers->add([
                    'Access-Control-Allow-Methods' => implode(', ', $routeMethods),
                    'Access-Control-Max-Age' => 86400,
                ]);
            }

            $response->headers->add(['Allow' => implode(', ', $routeMethods)]);

            $event->setResponse($response);
        }
    }

    public function onKernelResponse(ResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        $origin = $request->headers->get('origin', '*');
        if (preg_match($this->params->get('app.cors_origin'), $origin)) {
            $response->headers->add([
                'Access-Control-Allow-Origin' => $origin,
                'Access-Control-Allow-Headers' => 'X-Requested-With, Content-Type',
                'Access-Control-Allow-Credentials' => 'true',
            ]);
        }

        $event->setResponse($response);
    }
}
