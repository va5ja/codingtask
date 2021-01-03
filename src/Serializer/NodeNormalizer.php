<?php declare(strict_types=1);

namespace App\Serializer;

use App\Entity\Node;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ContextAwareDenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class NodeNormalizer extends ObjectNormalizer implements ContextAwareNormalizerInterface, ContextAwareDenormalizerInterface
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
            'graphId' => function ($innerObject) {
                return is_object($innerObject) ? $innerObject->toBase32() : $innerObject;
            }
        ];

        $context[AbstractNormalizer::IGNORED_ATTRIBUTES] = ['graphId'];

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
        return $data instanceof Node;
    }

    /**
     * @inheritdoc
     */
    public function supportsDenormalization($data, string $type, string $format = null, array $context = [])
    {
        return $type === Node::class;
    }
}
