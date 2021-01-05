<?php declare(strict_types=1);

namespace App\EntityManager\Metadata\Extractor;

use App\Annotation\EntityAnnotationInterface;
use Doctrine\Common\Annotations\Reader;

class MetadataExtractor
{
    /** @var Reader */
    private $reader;

    /** @var ExtractorInterface[] */
    private $extractors;

    public function __construct(Reader $reader, iterable $extractors)
    {
        $this->reader = $reader;
        $this->extractors = $extractors;
    }

    public function extractMetadataForClass(string $entityClassName): array
    {
        $metadata = [];
        $reflectionClass = new \ReflectionClass($entityClassName);

        $this->extractMetadata($this->getClassAnnotations($reflectionClass), $metadata);
        $this->extractMetadata($this->getClassPropertiesAnnotations($reflectionClass), $metadata);
        $this->extractMetadata($this->getClassMethodsAnnotations($reflectionClass), $metadata);

        return $metadata;
    }

    private function getClassAnnotations(\ReflectionClass $reflectionClass): \Iterator
    {
        foreach ($this->reader->getClassAnnotations($reflectionClass) as $annotation) {
            yield $reflectionClass->getShortName() => $annotation;
        }
    }

    private function getClassPropertiesAnnotations(\ReflectionClass $reflectionClass): \Iterator
    {
        foreach ($reflectionClass->getProperties() as $property) {
            foreach ($this->reader->getPropertyAnnotations($property) as $annotation) {
                yield $property->getName() => $annotation;
            }
        }
    }

    private function getClassMethodsAnnotations(\ReflectionClass $reflectionClass): \Iterator
    {
        foreach ($reflectionClass->getMethods() as $method) {
            foreach ($this->reader->getMethodAnnotations($method) as $annotation) {
                yield $method->getName() => $annotation;
            }
        }
    }

    private function extractMetadata(\Iterator $annotations, array &$metadata)
    {
        foreach ($annotations as $target => $annotation) {
            foreach ($this->extractors as $extractor) {
                if ($annotation instanceof EntityAnnotationInterface && $extractor->isApplicable($annotation)) {
                    $extractor->extract($annotation, $target, $metadata);
                }
            }
        }
    }
}
