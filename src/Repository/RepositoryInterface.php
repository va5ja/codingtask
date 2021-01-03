<?php declare(strict_types=1);

namespace App\Repository;

use UnexpectedValueException;

interface RepositoryInterface
{
    /**
     * Finds an object by its primary key / identifier.
     *
     * @param mixed $id
     * @return object|null
     */
    public function find($id): ?object;

    /**
     * Finds all objects in the repository.
     *
     * @return array
     */
    public function findAll(): array;

    /**
     * Finds objects by a set of criteria.
     *
     * Optionally sorting and limiting details can be passed. An implementation may throw
     * an UnexpectedValueException if certain values of the sorting or limiting details are
     * not supported.
     *
     * @param array $criteria
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return array
     * @throws UnexpectedValueException
     */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array;

    /**
     * Finds a single object by a set of criteria.
     *
     * @param array $criteria
     * @return object|null
     */
    public function findOneBy(array $criteria): ?object;

    /**
     * Counts entities by a set of criteria.
     *
     * @param array $criteria
     * @return int
     */
    public function count(array $criteria): int;

    /**
     * Returns the class name of the object managed by the repository.
     *
     * @return string
     */
    public function getClassName(): string;
}
