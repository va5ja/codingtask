<?php declare(strict_types=1);

namespace App\Request\Action;

use App\Request\Request;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

trait AbstractRequestActionTrait
{
    /**
     * Generates the "Location" or "Link" header depending on the data content
     *
     * @param array $data
     * @param Request $request
     * @return array|string[]
     */
    protected function generateLocationHeader(array $data, Request $request)
    {
        $entityClassName = $request->getEntityClassName();
        $itemRouteName = preg_replace('/^(.+?_)([^_]+?)$/', '\1item', $request->getRouteName());
        $itemRouteDefaults = $this->routeCollectionService->getRouteDefaults($itemRouteName);
        $itemRouteIdentifiers = $itemRouteDefaults[Request::ATTRIBUTE_IDENTIFIERS] ?? [];
        $itemRouteRequirements = array_keys($this->routeCollectionService->getRouteRequirements($itemRouteName));
        $itemRouteParameters = $itemRouteIdentifiers ?
            (array_key_exists(0, $itemRouteIdentifiers) ?
                [$entityClassName => $itemRouteIdentifiers] :
                $itemRouteIdentifiers) :
            [$entityClassName => $itemRouteRequirements];

        $links = [];
        foreach ($data as $entity) {
            $parameters = [];
            foreach ((array)$itemRouteParameters[$entityClassName] as $parameter) {
                try {
                    $parameters[$parameter] = $this->getPropertyAccessor()->getValue($entity, $parameter);
                } catch (NoSuchPropertyException $e) {
                    // no such property
                }
            }

            $parameters = $this->uuidCollectionService->encodeEntityProperties($entityClassName, $parameters) +
                $request->getRouteParameters();

            try {
                $links[] = $this->router->generate($itemRouteName, $parameters, UrlGeneratorInterface::ABSOLUTE_URL);
            } catch (RouteNotFoundException $e) {
                // route not found
            }
        }

        return count($links) === 1 ? ['Location' => $links[0]] : ['Link' => '<' . implode('>, <', $links) . '>'];
    }
}
