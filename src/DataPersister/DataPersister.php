<?php declare(strict_types=1);

namespace App\DataPersister;

use App\Exception\InvalidArgumentException;
use App\Request\Request;

class DataPersister
{
    /** @var DataPersisterInterface[] */
    private $persisters = [];

    public function __construct(iterable $persisters)
    {
        $this->persisters = $persisters;
    }

    public function saveData(array $data, Request $request)
    {
        foreach ($this->persisters as $persister) {
            if ($persister->isApplicable($data, $request)) {
                return $persister->persistData($data, $request);
            }
        }

        throw new InvalidArgumentException('Unsupported or missing data persister.');
    }
}
