<?php declare(strict_types=1);

namespace App\Annotation;

use App\Exception\InvalidArgumentException;
use Doctrine\Common\Annotations\Annotation;

/**
 * Annotation class for @Uuid().
 *
 * @Annotation
 * @Target("PROPERTY")
 */
final class Uuid
{
    public $version;
    public $encode;

    public function __construct(array $options = [])
    {
        foreach ($options as $key => $value) {
            if (!property_exists($this, $key)) {
                throw new InvalidArgumentException(
                    sprintf('Property "%s" does not exist on the Uuid annotation.', $key)
                );
            }

            $this->$key = $value;
        }
    }
}

