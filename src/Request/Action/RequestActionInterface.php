<?php declare(strict_types=1);

namespace App\Request\Action;

use App\Request\Request;

interface RequestActionInterface
{
    /**
     * Method that checks if the strategy is applicable
     *
     * @param Request $request
     * @return bool
     */
    public function isApplicable(Request $request): bool;

    /**
     * Method that performs the processing
     *
     * @param Request $request
     * @return mixed
     */
    public function process(Request $request);
}
