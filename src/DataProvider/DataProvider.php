<?php declare(strict_types=1);

namespace App\DataProvider;

use App\Exception\InvalidArgumentException;
use App\Request\Request;

class DataProvider
{
    /** @var DataProviderInterface[] */
    private $providers = [];

    public function __construct(iterable $providers)
    {
        $this->providers = $providers;
    }

    public function getData(Request $request)
    {
        foreach ($this->providers as $provider) {
            if ($provider->isApplicable($request)) {
                return $provider->provideData($request);
            }
        }

        throw new InvalidArgumentException('Unsupported or missing data provider.');
    }
}
