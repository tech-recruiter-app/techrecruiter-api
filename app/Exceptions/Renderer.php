<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Traits\FormatsExceptionResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Throwable;

final class Renderer
{
    use FormatsExceptionResponse;

    public function render(Throwable $e, Request $request): JsonResponse
    {
        return match (true) {
            $e instanceof AuthenticationException => $this->getExceptionResponse(
                401,
                'Authentication Required',
                'Access token is missing or invalid.',
                $request->getRequestUri(),
            ),
            $e instanceof AccessDeniedHttpException => $this->getExceptionResponse(
                403,
                'Access Denied',
                $e->getMessage() ?: 'You are not authorized to perform this action.',
                $request->getRequestUri(),
            ),
            $e instanceof NotFoundHttpException => $this->getExceptionResponse(
                $e->getStatusCode(),
                'Resource Not Found',
                $e->getMessage(),
                $request->getRequestUri(),
            ),
            $e instanceof MethodNotAllowedHttpException => $this->getExceptionResponse(
                $e->getStatusCode(),
                'Method Not Allowed',
                $e->getMessage(),
                $request->getRequestUri(),
            ),
            $e instanceof ValidationException => $this->getExceptionResponse(
                422,
                'Unprocessable Payload',
                'The request could not be processed due to validation errors.',
                $request->getRequestUri(),
                extensions: ['errors' => $e->errors()]
            ),
            $e instanceof TooManyRequestsHttpException => $this->getExceptionResponse(
                $e->getStatusCode(),
                'Too Many Requests',
                $e->getMessage(),
                $request->getRequestUri(),
            ),
            default => $this->getExceptionResponse(
                500,
                'Internal Server Error',
                'An unexpected error occurred while processing the request.',
                $request->getRequestUri()
            ),
        };
    }
}
