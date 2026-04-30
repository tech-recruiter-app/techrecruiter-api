<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait FormatsExceptionResponse
{
    /**
     * Formats an exception response.
     *
     * @param  int  $status  The HTTP status code for the response.
     * @param  string  $title  A short, human-readable summary of the problem type.
     * @param  string  $detail  A human-readable explanation specific to this occurrence of the problem.
     * @param  string  $instance  A URI reference that identifies the specific occurrence of the problem.
     * @param  string|null  $type  An optional URI reference that identifies the problem type.
     * @param  array<string, mixed>  $extensions  An optional array of additional data to include in the response.
     * @return JsonResponse A JSON response containing the formatted exception details.
     */
    private function getExceptionResponse(
        int $status,
        string $title,
        string $detail,
        string $instance,
        ?string $type = null,
        array $extensions = []
    ): JsonResponse {
        $data = [
            'status' => $status,
            'title' => $title,
            'detail' => $detail,
            'instance' => $instance,
        ];

        if ($type !== null) {
            $data['type'] = $type;
        }
        if ($extensions !== []) {
            $data = array_merge($data, $extensions);
        }

        return new JsonResponse($data, $status);
    }
}
