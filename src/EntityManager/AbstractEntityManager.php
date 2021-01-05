<?php declare(strict_types=1);

namespace App\EntityManager;

use App\Entity\AbstractObservableEntity;
use App\EntityManager\Metadata\Extractor\IdExtractor;
use App\EntityManager\Metadata\MetadataProvider;
use App\Exception\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use SplSubject;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

abstract class AbstractEntityManager implements \SplObserver
{
    protected const ACTION_CREATE = 'create';
    protected const ACTION_READ = 'read';
    protected const ACTION_UPDATE = 'update';
    protected const ACTION_DELETE = 'delete';

    protected const CONTEXT_CHANGES = 'changes';

    protected const REPOSITORY_SUFFIX = 'Repository';

    /** @var LoggerInterface */
    protected $logger;

    /** @var MetadataProvider */
    protected $metadataProvider;

    /** @var PropertyAccessorInterface */
    protected $propertyAccessor;

    protected $supportedEntities = [];

    protected $managedEntities = [];

    protected $modifiedEntities = [];

    protected $persistedEntities = [];

    protected $identifiedEntities = [];

    protected $changesQueue = [
        self::ACTION_CREATE => [],
        self::ACTION_READ => [],
        self::ACTION_UPDATE => [],
        self::ACTION_DELETE => []
    ];

    public function __construct(LoggerInterface $logger, MetadataProvider $metadataProvider)
    {
        $this->logger = $logger;
        $this->metadataProvider = $metadataProvider;
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
        if ($id === '' || $id === null || $id === []) {
            throw new InvalidArgumentException('No identifiers specified for entity "' . $className . '".');
        }

        if (!is_array($id)) {
            $classMetadata = $this->getClassMetadata($className);
            $identifiers = $classMetadata[IdExtractor::KEY_EXTRACTION] ?? [];

            if (!$identifiers || count($identifiers) > 1) {
                throw new InvalidArgumentException(
                    'Entity "' . $className . '" has 0 or more than 1 identifier ("' . implode('", "',
                        array_keys($identifiers)) . '").'
                );
            }

            $id = [reset($identifiers) => $id];
        }

        ksort($id);
        $idHash = implode(' ', $id);

        if (isset($this->managedEntities[$className][$idHash])) {
            return $this->managedEntities[$className][$idHash];
        }

        $repository = $this->getRepository($className);

        if (($object = $repository->find($id)) !== null) {
            if ($object instanceof \SplSubject) {
                $object->attach($this);
            }

            $this->managedEntities[$className][$idHash] = $object;
            $this->identifiedEntities[spl_object_hash($object)] = $id;
        }

        return $object;
    }

    /**
     * @inheritdoc
     */
    public function persist(object $object): void
    {
        $className = get_class($object);
        $idHash = $this->getObjectIdHash($object);

        if ($this->hasObjectIdHash($object) && !isset($this->managedEntities[$className][$idHash])) {
            $this->managedEntities[$className][$idHash] = $object;
        }

        if (!$this->hasObjectIdHash($object)) {
            $this->persistedEntities[] = $object;
        }
    }

    /**
     * @inheritdoc
     */
    public function remove(object $object): void
    {
        if ($this->hasObjectIdHash($object)) {
            $this->changesQueue[self::ACTION_DELETE][] = $object;
        }
    }

    /**
     * @inheritdoc
     */
    public function clear(?string $objectName = null): void
    {
        if ($objectName !== null) {
            unset($this->managedEntities[$objectName]);
        } else {
            $this->managedEntities = [];
        }
    }

    /**
     * @inheritdoc
     */
    public function refresh(object $object): void
    {
        $className = get_class($object);

        if (!$this->hasObjectIdHash($object) || !$this->isManagedObject($object)) {
            return;
        }

        $identifiers = $this->getObjectIdentifiers($object);
        $idHash = $this->getObjectIdHash($object);

        unset($this->managedEntities[$className][$idHash]);

        if (($foundObject = $this->find($className, $identifiers)) !== null) {
            $this->managedEntities[$className][$idHash] = $foundObject;
            $this->identifiedEntities[spl_object_hash($foundObject)] = $identifiers;
        }
    }

    /**
     * @inheritdoc
     */
    public function flush(): void
    {
        $this->preparePersistedEntities();

        $this->prepareManagedEntities();

        $this->processChangesQueue();

        $this->resetEntityManager();
    }

    /**
     * @inheritdoc
     */
    public function getClassMetadata(string $className): array
    {
        return $this->metadataProvider->getClassMetadata($className);
    }

    /**
     * @inheritdoc
     */
    public function contains(object $object): bool
    {
        return $this->isManagedObject($object);
    }

    /**
     * @inheritdoc
     */
    public function update(SplSubject $subject, string $event = null, $data = null): void
    {
        $className = get_class($subject);
        $idHash = $this->getObjectIdHash($subject);

        if ($event === AbstractObservableEntity::EVENT_PROPERTY_CHANGE) {
            $changes = $this->modifiedEntities[$className][$idHash] ?? [];
            $this->modifiedEntities[$className][$idHash] = array_unique(array_merge($changes, [$data['property']]));
        }
    }

    protected function isManagedObject(object $object): bool
    {
        $className = get_class($object);
        $idHash = $this->getObjectIdHash($object);

        return isset($this->managedEntities[$className][$idHash]);
    }

    protected function getObjectIdentifiers(object $object): array
    {
        $objectHash = spl_object_hash($object);

        if (!isset($this->identifiedEntities[$objectHash])) {
            $className = get_class($object);
            $classMetadata = $this->getClassMetadata($className);
            $identifiers = $classMetadata[IdExtractor::KEY_EXTRACTION] ?? [];

            $ids = [];
            foreach (array_keys($identifiers) as $identifier) {
                $ids[$identifier] = $this->getPropertyAccessor()->getValue($object, $identifier);
            }

            ksort($ids);
            $this->identifiedEntities[$objectHash] = $ids;
        }

        return $this->identifiedEntities[$objectHash];
    }

    protected function getObjectIdHash(object $object): string
    {
        return implode(' ', $this->getObjectIdentifiers($object));
    }

    protected function hasObjectIdHash(object $object): bool
    {
        return $this->getObjectIdHash($object) !== '';
    }

    protected function preparePersistedEntities(): void
    {
        foreach ($this->persistedEntities as $persistedObject) {
            $this->changesQueue[self::ACTION_CREATE][] = $persistedObject;
        }
    }

    protected function prepareManagedEntities(): void
    {
        foreach ($this->managedEntities as $className => $managedObjects) {
            foreach ($managedObjects as $idHash => $managedObject) {
                if (isset($this->modifiedEntities[$className][$idHash]) && $this->modifiedEntities[$className][$idHash]) {
                    $this->changesQueue[self::ACTION_UPDATE][] = $managedObject;
                }
            }
        }
    }

    protected function processChangesQueue(): void
    {
        foreach ($this->changesQueue as $action => $queue) {
            foreach ($queue as $object) {
                $className = get_class($object);
                $idHash = $this->getObjectIdHash($object);

                try {
                    $method = $action . (new \ReflectionClass($className))->getShortName();
                } catch (\ReflectionException $e) {
                    $this->logger->warning('Reflection exception: ' . $e->getMessage());
                    continue;
                }

                $repository = $this->getRepository($className);

                $this->throwExceptionIfRepositoryHasNoMethod($repository, $method);

                $context = [];
                if ($action === self::ACTION_UPDATE) {
                    $context[self::CONTEXT_CHANGES] = $this->modifiedEntities[$className][$idHash];
                }

                $repository->$method($object, $context);
            }
        }
    }

    protected function resetEntityManager(): void
    {
        $this->persistedEntities = [];
        $this->modifiedEntities = [];

        foreach ($this->changesQueue as $action => $queue) {
            $this->changesQueue[$action] = [];
        }
    }

    protected function throwExceptionIfRepositoryHasNoMethod(object $repository, string $methodName)
    {
        if (!method_exists($repository, $methodName)) {
            throw new \BadMethodCallException(
                sprintf('Repository "%s" does not implement method "%s".', get_class($repository), $methodName)
            );
        }
    }

    protected function getPropertyAccessor(): PropertyAccessorInterface
    {
        if (null === $this->propertyAccessor) {
            $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
        }

        return $this->propertyAccessor;
    }
}
