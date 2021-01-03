<?php declare(strict_types=1);

namespace App\EntityManager;

use App\Exception\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;

abstract class AbstractEntityManager
{
    protected const ACTION_CREATE = 'create';
    protected const ACTION_READ = 'read';
    protected const ACTION_UPDATE = 'update';
    protected const ACTION_DELETE = 'delete';

    protected const REPOSITORY_SUFFIX = 'Repository';

    /** @var ServiceLocator */
    protected $locator;

    /** @var LoggerInterface */
    protected $logger;

    protected $supportedEntities = [];

    protected $managedObjects = [];

    protected $changesQueue = [
        self::ACTION_CREATE => [],
        self::ACTION_READ => [],
        self::ACTION_UPDATE => [],
        self::ACTION_DELETE => []
    ];

    public function __construct(ServiceLocator $locator, LoggerInterface $logger)
    {
        $this->locator = $locator;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function supports(string $entityClassName): bool
    {
        return in_array($entityClassName, $this->supportedEntities);
    }

    /**
     * @inheritdoc
     */
    public function find(string $className, $id): ?object
    {
        $hashedId = spl_object_hash((object)$id);

        if (
            array_key_exists($className, $this->managedObjects) &&
            array_key_exists($hashedId, $this->managedObjects[$className])
        ) {
            return $this->managedObjects[$className][$hashedId];
        }

        $repository = $this->getRepository($className);

        if (($object = $repository->find($id)) !== null) {
            $this->managedObjects[$className][$hashedId] = $object;
        }

        return $object;
    }

    /**
     * @inheritdoc
     */
    public function persist(object $object): void
    {
        $this->changesQueue[$this->isManagedObject($object) || $object->getId() !== null ? self::ACTION_UPDATE : self::ACTION_CREATE][] = $object;
    }

    /**
     * @inheritdoc
     */
    public function remove(object $object): void
    {
        $this->changesQueue[self::ACTION_DELETE][] = $object;
    }

    /**
     * @inheritdoc
     */
    public function clear(?string $objectName = null): void
    {
        if ($objectName !== null) {
            unset($this->managedObjects[$objectName]);
        } else {
            $this->managedObjects = [];
        }
    }

    /**
     * @inheritdoc
     */
    public function refresh(object $object): void
    {
        $objectName = get_class($object);

        if (!$this->isManagedObject($object)) {
            return;
        }

        unset($this->managedObjects[$objectName][$object->getId()]);

        if (($foundObject = $this->find($objectName, $object->getId())) !== null) {
            $this->managedObjects[$objectName][$object->getId()] = $foundObject;
        }
    }

    /**
     * @inheritdoc
     */
    public function flush($entity = null): void
    {
        foreach ($this->changesQueue as $action => $queue) {
            foreach ($queue as $object) {
                $objectName = get_class($object);

                try {
                    $method = $action . (new \ReflectionClass($objectName))->getShortName();
                } catch (\ReflectionException $e) {
                    $this->logger->warning('Reflection exception: ' . $e->getMessage());
                    continue;
                }

                $repository = $this->getRepository($objectName);

                $this->throwExceptionIfRepositoryHasNoMethod($repository, $method);

                $repository->$method($object);

                unset($this->managedObjects[$objectName][(string)$object->getId()]);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function getRepository(string $className): object
    {
        $repository = 'App\\Repository\\' . (new \ReflectionClass($className))->getShortName() . self::REPOSITORY_SUFFIX;

        if ($this->locator->has($repository)) {
            return $this->locator->get($repository);
        }

        throw new InvalidArgumentException("Repository \"$repository\" for entity \"$className\" does not exist.");
    }

    /**
     * @inheritdoc
     */
    public function contains(object $object): bool
    {
        return $this->isManagedObject($object);
    }

    private function isManagedObject(object $object): bool
    {
        $objectName = get_class($object);

        return array_key_exists($objectName, $this->managedObjects) &&
            array_key_exists((string)$object->getId(), $this->managedObjects[$objectName]);
    }

    private function throwExceptionIfRepositoryHasNoMethod(object $repository, string $methodName)
    {
        if (!method_exists($repository, $methodName)) {
            throw new \BadMethodCallException(
                sprintf('Repository "%s" does not implement method "%s".', get_class($repository), $methodName)
            );
        }
    }
}
