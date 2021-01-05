<?php declare(strict_types=1);

namespace App\EntityManager\Metadata\Extractor;

use App\Annotation\EntityAnnotationInterface;
use App\Annotation\Id;

class IdExtractor implements ExtractorInterface
{
    public const KEY_EXTRACTION = 'identifiers';
    public const KEY_TYPE = 'type';
    public const KEY_VERSION = 'version';
    public const KEY_ENCODE = 'encode';

    public function extract(EntityAnnotationInterface $annotation, string $target, array &$classMetadata)
    {
        /** @var Id $annotation */
        $classMetadata[self::KEY_EXTRACTION][$target] = [
            self::KEY_TYPE => $annotation->type,
            self::KEY_VERSION => $annotation->version,
            self::KEY_ENCODE => $annotation->encode,
        ];
    }

    public function isApplicable(EntityAnnotationInterface $annotation): bool
    {
        return $annotation instanceof Id;
    }
}
