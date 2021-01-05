<?php declare(strict_types=1);

namespace App\EntityManager\Metadata\Extractor;

use App\Annotation\EntityAnnotationInterface;

interface ExtractorInterface
{
    /**
     * Method that checks if the extractor is applicable.
     *
     * @param EntityAnnotationInterface $annotation
     * @return bool
     */
    public function isApplicable(EntityAnnotationInterface $annotation): bool;

    /**
     * Method that performs the extraction.
     *
     * @param EntityAnnotationInterface $annotation
     * @param string $target
     * @param array $classMetadata
     * @return mixed
     */
    public function extract(EntityAnnotationInterface $annotation, string $target, array &$classMetadata);
}
