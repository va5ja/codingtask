<?php declare(strict_types=1);

namespace App\EntityManager;

interface EntityManagerInterface
{
    /**
     * Checks if the manager supports the specified entity.
     *
     * @param string $entityClassName
     * @return bool
     */
    public function supports(string $entityClassName): bool;

    /**
     * Finds an object by its identifier.
     *
     * @param string $className
     * @param mixed $id
     * @return object|null
     */
    public function find(string $className, $id): ?object;

    /**
     * Tells the manager to make an instance managed and persistent.
     *
     * The object will be entered into the database as a result of the flush operation.
     *
     * NOTE: The persist operation always considers objects that are not yet known to
     * this ObjectManager as NEW. Do not pass detached objects to the persist operation.
     *
     * @param object $object
     * @return void
     */
    public function persist(object $object): void;

    /**
     * Removes an object instance.
     *
     * A removed object will be removed from the database as a result of the flush operation.
     *
     * @param object $object
     * @return void
     */
    public function remove(object $object): void;

    /**
     * Clears the manager.
     *
     * All objects (or of specified type) currently managed by this manager become detached.
     *
     * @param string|null $objectName
     * @return void
     */
    public function clear(?string $objectName = null): void;

    /**
     * Refreshes the persistent state of an object from the database,
     * overriding any local changes that have not yet been persisted.
     *
     * @param object $object
     * @return void
     */
    public function refresh(object $object): void;

    /**
     * Flushes all changes to objects that have been queued up to now to the database.
     * This effectively synchronizes the in-memory state of managed objects with the
     * database.
     *
     * @return void
     */
    public function flush(): void;

    /**
     * Gets the repository for a class.
     *
     * @param string $className
     * @return object
     */
    public function getRepository(string $className): object;

    /**
     * Checks if the object is managed.
     *
     * @param object $object
     * @return bool
     */
    public function contains(object $object): bool;
}
