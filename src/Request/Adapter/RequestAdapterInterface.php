<?php declare(strict_types=1);

namespace App\Request\Adapter;

interface RequestAdapterInterface
{
    /**
     * Get the request URI
     *
     * @return string
     */
    public function getUri(): string;

    /**
     * Get the request method
     *
     * @return string
     */
    public function getMethod(): string;

    /**
     * Get route name
     *
     * @return string
     */
    public function getRouteName(): ?string;

    /**
     * Get route parameters
     *
     * @return array
     */
    public function getRouteParameters(): array;

    /**
     * Get the query string parameters
     *
     * @return array
     */
    public function getQueryParameters(): array;

    /**
     * Get the request payload
     *
     * @return string
     */
    public function getContent(): string;

    /**
     * Get entity class name
     *
     * @return string|null
     */
    public function getEntityClassName(): ?string;

    /**
     * Get data provider type
     *
     * @return string|null
     */
    public function getDataProviderType(): ?string;

    /**
     * Get identifiers
     *
     * @return array
     */
    public function getIdentifiers(): array;
}
