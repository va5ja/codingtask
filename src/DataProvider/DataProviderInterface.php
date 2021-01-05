<?php declare(strict_types=1);

namespace App\DataProvider;

use App\Request\Request;

interface DataProviderInterface
{
    /**
     * Method that checks if the provider is applicable
     *
     * @param Request $request
     * @return bool
     */
    public function isApplicable(Request $request): bool;

    /**
     * Method that provides the data
     *
     * @param Request $request
     * @return mixed
     */
    public function provideData(Request $request);
}
