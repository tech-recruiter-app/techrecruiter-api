<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CreateJobPosting;
use App\Actions\DeleteJobPosting;
use App\Actions\UpdateJobPosting;
use App\Data\JobPostingUpdateData;
use App\Data\NewJobPostingData;
use App\Filters\JobLocationFilter;
use App\Filters\JobQueryFilter;
use App\Filters\JobTypeFilter;
use App\Filters\RecencyFilter;
use App\Http\Requests\FetchJobPostingsRequest;
use App\Http\Requests\SaveJobPostingRequest;
use App\Models\JobPosting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Pipeline;
use RuntimeException;
use Stevebauman\Location\Facades\Location;
use Stevebauman\Location\Position;

final class JobPostingController extends Controller
{
    public function index(FetchJobPostingsRequest $request): JsonResponse
    {
        $currentLocation = function () use ($request): string {
            $position = Location::get();

            if (! $position instanceof Position || is_null($position->countryName)) {
                throw new RuntimeException("Unable to determine the user's current location with IP address [{$request->ip()}]");
            }

            return is_null($position->cityName) ?
                $position->countryName :
                sprintf('%s, %s', $position->cityName, $position->countryName);
        };

        $query = Pipeline::send(JobPosting::active())
            ->through([
                new JobQueryFilter($request->validated('query')),
                new JobLocationFilter($request->validated('location', $currentLocation())), // Use the current location if no location is provided
                new JobTypeFilter($request->validated('type')),
                new RecencyFilter($request->safe()->integer('recency', 14)), // Default to 14 days if no recency is provided
            ])
            ->thenReturn();

        assert($query instanceof Builder);

        return $query
            ->latest()
            ->paginate()
            ->toResourceCollection()
            ->response();
    }

    public function show(JobPosting $jobPosting): JsonResponse
    {
        return $jobPosting->toResource()->response();
    }

    public function store(SaveJobPostingRequest $request, CreateJobPosting $action): JsonResponse
    {
        $jobPostingData = $request->toDto();
        assert($jobPostingData instanceof NewJobPostingData);
        $jobPosting = $action->handle($jobPostingData);

        return $jobPosting->toResource()->response()->setStatusCode(201);
    }

    public function update(SaveJobPostingRequest $request, JobPosting $jobPosting, UpdateJobPosting $action): JsonResponse
    {
        $jobPostingData = $request->toDto();
        assert($jobPostingData instanceof JobPostingUpdateData);
        $jobPosting = $action->handle($jobPosting, $jobPostingData);

        return $jobPosting->toResource()->response();
    }

    public function delete(JobPosting $jobPosting, DeleteJobPosting $action): JsonResponse
    {
        $action->handle($jobPosting);

        return response()->json(status: 204);
    }
}
