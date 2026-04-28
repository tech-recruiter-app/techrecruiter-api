<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\RegisterUser;
use App\Data\EmployerRegistrationData;
use App\Data\JobseekerRegistrationData;
use App\Http\Requests\EmployerRegistrationRequest;
use App\Http\Requests\JobseekerRegistrationRequest;
use Illuminate\Http\JsonResponse;

final class UserController extends Controller
{
    public function registerEmployer(EmployerRegistrationRequest $request, RegisterUser $action): JsonResponse
    {
        $employer = $action->execute(
            EmployerRegistrationData::createFromRequest($request)
        );

        return $employer->load('profile')->toResource()->response()->setStatusCode(201);
    }

    public function registerJobseeker(JobseekerRegistrationRequest $request, RegisterUser $action): JsonResponse
    {
        $jobseeker = $action->execute(
            JobseekerRegistrationData::createFromRequest($request)
        );

        return $jobseeker->load('profile')->toResource()->response()->setStatusCode(201);
    }
}
