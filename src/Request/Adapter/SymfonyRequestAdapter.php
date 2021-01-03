<?php declare(strict_types=1);

namespace App\Request\Adapter;

use App\Request\Request;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\RequestStack;

final class SymfonyRequestAdapter implements RequestAdapterInterface
{
    /** @var RequestStack */
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritdoc}
     */
    public function getUri(): string
    {
        return $this->getRequest()->getUri();
    }

    /**
     * {@inheritdoc}
     */
    public function getMethod(): string
    {
        return $this->getRequest()->getMethod();
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteName(): ?string
    {
        return $this->getRequest()->attributes->get('_route');
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteParameters(): array
    {
        return $this->getRequest()->attributes->get('_route_params', []);
    }

    /**
     * {@inheritdoc}
     */
    public function getQueryParameters(): array
    {
        return $this->getRequest()->query->all();
    }

    /**
     * {@inheritdoc}
     */
    public function getContent(): string
    {
        return $this->getRequest()->getContent();
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityClassName(): ?string
    {
        return $this->getRequest()->attributes->get(Request::ATTRIBUTE_ENTITY);
    }

    /**
     * {@inheritdoc}
     */
    public function getDataProviderType(): ?string
    {
        return $this->getRequest()->attributes->get(Request::ATTRIBUTE_PROVIDES);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifiers(): array
    {
        return (array)$this->getRequest()->attributes->get(Request::ATTRIBUTE_IDENTIFIERS, []);
    }

    private function getRequest(): ?SymfonyRequest
    {
        return $this->requestStack->getCurrentRequest();
    }
}
