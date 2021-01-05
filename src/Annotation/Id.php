<?php declare(strict_types=1);

namespace App\Annotation;

use App\Exception\InvalidArgumentException;
use Doctrine\Common\Annotations\Annotation;

/**
 * Annotation class for @Id().
 *
 * @Annotation
 * @Target("PROPERTY")
 */
final class Id implements EntityAnnotationInterface
{
    public $type;
    public $version;
    public $encode;

    public function __construct(array $options = [])
    {
        foreach ($options as $key => $value) {
            if (!property_exists($this, $key)) {
                throw new InvalidArgumentException(
                    sprintf('Property "%s" does not exist on the Id annotation.', $key)
                );
            }

            $this->$key = $value;
        }
    }
}

