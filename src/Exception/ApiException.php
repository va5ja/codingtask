<?php declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class ApiException extends HttpException
{
    public function __construct(
        int $statusCode,
        string $message = null,
        \Throwable $previous = null,
        ?int $code = 0,
        array $headers = []
    ) {
        parent::__construct($statusCode, $message, $previous, $headers, $code);
    }
}
