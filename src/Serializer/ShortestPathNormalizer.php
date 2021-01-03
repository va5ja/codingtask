<?php declare(strict_types=1);

namespace App\Serializer;

use App\Entity\ShortestPath;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ContextAwareDenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Uid\Uuid;

class ShortestPathNormalizer extends ObjectNormalizer implements ContextAwareNormalizerInterface, ContextAwareDenormalizerInterface
{
    /** @var ObjectNormalizer */
    private $normalizer;

    /** @var Security */
    private $security;

    public function __construct(
        ObjectNormalizer $normalizer,
        Security $security
    ) {
        $this->normalizer = $normalizer;
        $this->security = $security;
    }

    /**
     * @inheritdoc
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $context[AbstractNormalizer::CALLBACKS] = [
            'id' => function ($innerObject) {
                return is_object($innerObject) ? $innerObject->toBase32() : $innerObject;
            },
            'fromNode' => function ($id) {
                return Uuid::fromString($id)->toBase32();
            },
            'toNode' => function ($id) {
                return Uuid::fromString($id)->toBase32();
            },
            'createdAt' => function ($innerObject) {
                return is_object($innerObject) ? $innerObject->format(\DateTime::ATOM) : $innerObject;
            },
            'updatedAt' => function ($innerObject) {
                return is_object($innerObject) ? $innerObject->format(\DateTime::ATOM) : $innerObject;
            }
        ];

        $context[AbstractNormalizer::IGNORED_ATTRIBUTES] = ['graph'];

        return $this->normalizer->normalize($object, $format, $context);
    }

    /**
     * @inheritdoc
     */
    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        return $this->normalizer->denormalize($data, $type, $format, $context);
    }

    /**
     * @inheritdoc
     */
    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof ShortestPath;
    }

    /**
     * @inheritdoc
     */
    public function supportsDenormalization($data, string $type, string $format = null, array $context = [])
    {
        return $type === ShortestPath::class;
    }
}
