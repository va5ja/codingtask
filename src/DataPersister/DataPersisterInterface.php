<?php declare(strict_types=1);

namespace App\DataPersister;

use App\Request\Request;

interface DataPersisterInterface
{
    /**
     * Method that checks if the persister is applicable.
     *
     * @param array $data
     * @param Request $request
     * @return bool
     */
    public function isApplicable(array $data, Request $request): bool;

    /**
     * Method that persists the data.
     *
     * @param array $data
     * @param Request $request
     * @return mixed
     */
    public function persistData(array $data, Request $request): array;
}
