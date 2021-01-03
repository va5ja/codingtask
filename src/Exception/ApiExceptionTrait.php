<?php declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

trait ApiExceptionTrait
{
    public function throwApiException(
        ?string $message,
        int $statusCode = Response::HTTP_BAD_REQUEST,
        ?\Throwable $previous = null
    ) {
        throw new ApiException($statusCode, $message, $previous);
    }
}