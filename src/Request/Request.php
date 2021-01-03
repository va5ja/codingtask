<?php declare(strict_types=1);

namespace App\Request;

use App\Request\Adapter\RequestAdapterInterface;
use App\Service\UuidCollectionService;

class Request
{
    public const METHOD_GET = 'GET';
    public const METHOD_POST = 'POST';
    public const METHOD_PATCH = 'PATCH';
    public const METHOD_DELETE = 'DELETE';

    public const ATTRIBUTE_ENTITY = 'entity';
    public const ATTRIBUTE_PROVIDES = 'provides';
    public const ATTRIBUTE_IDENTIFIERS = 'identifiers';

    public const PROVIDER_COLLECTION = 'collection';
    public const PROVIDER_ITEM = 'item';

    /** @var RequestAdapterInterface */
    private $requestAdapter;

    /** @var UuidCollectionService */
    private $uuidCollectionService;

    public function __construct(
        RequestAdapterInterface $requestAdapter,
        UuidCollectionService $uuidCollectionService
    ) {
        $this->requestAdapter = $requestAdapter;
        $this->uuidCollectionService = $uuidCollectionService;
    }

    public function getUri(): string
    {
        return $this->requestAdapter->getUri();
    }

    public function getMethod(): string
    {
        return $this->requestAdapter->getMethod();
    }

    public function getRouteName(): ?string
    {
        return $this->requestAdapter->getRouteName();
    }

    public function getRouteParameters(): array
    {
        return array_diff_key(
            $this->requestAdapter->getRouteParameters(),
            array_flip([
                self::ATTRIBUTE_ENTITY,
                self::ATTRIBUTE_PROVIDES,
                self::ATTRIBUTE_IDENTIFIERS,
            ])
        );
    }

    public function getRouteParameter(string $key, ?string $default = null): ?string
    {
        $parameters = $this->getRouteParameters();

        return array_key_exists($key, $parameters) ? $parameters[$key] : $default;
    }

    public function getQueryParameters(): array
    {
        return $this->requestAdapter->getQueryParameters();
    }

    /**
     * @return array|string|null
     */
    public function getQueryParameter(string $key, $default = null)
    {
        $parameters = $this->getQueryParameters();

        return array_key_exists($key, $parameters) ? $parameters[$key] : $default;
    }

    public function getContent(): string
    {
        return $this->requestAdapter->getContent();
    }

    public function getEntityClassName(): ?string
    {
        return $this->requestAdapter->getEntityClassName();
    }

    public function getDataProviderType(): ?string
    {
        return $this->requestAdapter->getDataProviderType();
    }

    public function getIdentifiers(): array
    {
        $routeIdentifiers = $this->requestAdapter->getIdentifiers();
        $routeParameters = $this->getRouteParameters();

        $entityIdentifiers = $routeIdentifiers ?
            (array_key_exists(0, $routeIdentifiers) ?
                [$this->getEntityClassName() => $routeIdentifiers] :
                $routeIdentifiers) :
            [$this->getEntityClassName() => array_keys($routeParameters)];

        foreach ($entityIdentifiers as $entityClass => $identifiers) {
            $identifiers = (array)$identifiers;
            $entityIdentifiers[$entityClass] = $this->uuidCollectionService->decodeEntityProperties(
                $entityClass,
                array_key_exists(0, $identifiers) ?
                    array_intersect_key($routeParameters, array_flip($identifiers)) :
                    array_combine(
                        array_keys($identifiers),
                        array_intersect_key($routeParameters, array_flip($identifiers))
                    )
            );
        }

        return $entityIdentifiers;
    }

    public function getEntityIdentifiers(): array
    {
        return $this->getIdentifiers()[$this->getEntityClassName()] ?? [];
    }
}
