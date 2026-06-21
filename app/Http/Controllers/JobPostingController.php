<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CreateJobPosting;
use App\Http\Requests\SaveJobPostingRequest;
use Illuminate\Http\JsonResponse;

final class JobPostingController extends Controller
{
    public function store(SaveJobPostingRequest $request, CreateJobPosting $action): JsonResponse
    {
        $jobPosting = $action->handle($request->toDto());

        return $jobPosting->toResource()->response()->setStatusCode(201);
    }
}
