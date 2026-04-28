<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Traits\FormatsExceptionResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

final class LoginFailedException extends RuntimeException
{
    use FormatsExceptionResponse;

    public function __construct()
    {
        parent::__construct('Invalid email or password.');
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render(Request $request): JsonResponse
    {
        return $this->getExceptionResponse(
            401,
            'Invalid authentication credentials.',
            $this->getMessage(),
            $request->getRequestUri()
        );
    }
}
