<?php declare(strict_types=1);

namespace App\Request\Action;

use App\DataPersister\DataPersister;
use App\DataProvider\DataProvider;
use App\EntityManager\EntityManagerProvider;
use App\Exception\ApiExceptionTrait;
use App\Request\Request;
use App\Service\RouteCollectionService;
use App\Service\UuidService;
use Symfony\Component\PropertyAccess\Exception\InvalidArgumentException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class AbstractRequestAction implements RequestActionInterface
{
    use ApiExceptionTrait;

    /** @var string */
    protected const APPLICABLE_METHOD = '';

    /** @var DataProvider */
    protected $dataProvider;

    /** @var DataPersister */
    protected $dataPersister;

    /** @var SerializerInterface */
    protected $serializer;

    /** @var Security */
    protected $security;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var EntityManagerProvider */
    protected $entityManager;

    /** @var PropertyAccessorInterface */
    protected $propertyAccessor;

    /** @var RouteCollectionService */
    protected $routeCollectionService;

    /** @var UuidService */
    protected $uuidService;

    /** @var UrlGeneratorInterface */
    protected $router;

    public function __construct(
        DataProvider $dataProvider,
        DataPersister $dataPersister,
        SerializerInterface $serializer,
        Security $security,
        ValidatorInterface $validator,
        EntityManagerProvider $entityManager,
        RouteCollectionService $routeCollectionService,
        UuidService $uuidService,
        UrlGeneratorInterface $router
    ) {
        $this->dataProvider = $dataProvider;
        $this->dataPersister = $dataPersister;
        $this->serializer = $serializer;
        $this->security = $security;
        $this->validator = $validator;
        $this->entityManager = $entityManager;
        $this->routeCollectionService = $routeCollectionService;
        $this->uuidService = $uuidService;
        $this->router = $router;
    }

    /**
     * @inheritdoc
     */
    public function isApplicable(Request $request): bool
    {
        return $request->getMethod() === static::APPLICABLE_METHOD;
    }

    protected function validate($data): void
    {
        if (count($errors = $this->validator->validate($data)) > 0) {
            $messages = [];
            /** @var ConstraintViolationInterface $error */
            foreach ($errors as $error) {
                $messages[] = 'Property "' . $error->getPropertyPath() . '": ' . $error->getMessage();
            }

            $this->throwApiException(implode(PHP_EOL, $messages));
        }
    }

    protected function mergeEntity($oldEntity, $newEntity, array $identifiers): object
    {
        $reflectionProperties = (new \ReflectionClass($oldEntity))->getProperties();

        foreach ($reflectionProperties as $reflectionProperty) {
            if (in_array($reflectionProperty->getName(), $identifiers)) {
                continue;
            }

            try {
                $this->getPropertyAccessor()->setValue(
                    $oldEntity,
                    $reflectionProperty->getName(),
                    $this->getPropertyAccessor()->getValue($newEntity, $reflectionProperty->getName())
                );
            } catch (InvalidArgumentException $e) {
                // skip
            }
        }

        return $oldEntity;
    }

    protected function getPropertyAccessor(): PropertyAccessorInterface
    {
        if (null === $this->propertyAccessor) {
            $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
        }

        return $this->propertyAccessor;
    }
}
