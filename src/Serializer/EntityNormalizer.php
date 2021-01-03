<?php declare(strict_types=1);

namespace App\Serializer;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareDenormalizerInterface;

class EntityNormalizer implements ContextAwareDenormalizerInterface
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @inheritdoc
     */
    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        return $this->entityManager->getReference($type, is_array($data) ? $data['id'] : $data);
    }

    /**
     * @inheritdoc
     */
    public function supportsDenormalization($data, string $type, string $format = null, array $context = []): bool
    {
        return strpos($type, 'App\\Entity\\') === 0 && (is_numeric($data) || is_string($data));
    }
}
