<?php declare(strict_types=1);

namespace App\Request\Action;

use App\Exception\InvalidArgumentException;
use App\Request\Request;

class RequestActionProcessor
{
    /** @var RequestActionInterface[] */
    private $requestActions;

    public function __construct(iterable $requestActions)
    {
        $this->requestActions = $requestActions;
    }

    public function process(Request $request)
    {
        foreach ($this->requestActions as $requestAction) {
            if ($requestAction->isApplicable($request)) {
                return $requestAction->process($request);
            }
        }

        throw new InvalidArgumentException('Unsupported request method.');
    }
}
