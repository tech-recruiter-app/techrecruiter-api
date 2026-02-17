<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\DomainNameVerifier as DomainNameVerifierContract;
use App\Contracts\LinkVerifier as LinkVerifierContract;
use App\Models\EmployerProfile;
use App\Models\JobSeekerProfile;
use App\Services\DomainNameVerifier;
use App\Services\LinkVerifier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use LogicException;
use Override;
use Uri\WhatWg\Url;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    #[Override]
    public function register(): void
    {
        // Register the link verifier
        $this->app->singleton(function (): LinkVerifierContract {
            if (! is_string($appUrl = config('app.url'))) {
                throw new LogicException('Application URL must be a string.');
            }
            if (
                is_null($appUrl = Url::parse($appUrl)) ||
                is_null($appHost = $appUrl->getAsciiHost()) ||
                $appHost === ''
            ) {
                throw new LogicException('Application URL must have a domain name.');
            }

            return new LinkVerifier($appHost);
        });

        // Register the domain name verifier
        $this->app->bind(fn (): DomainNameVerifierContract => new DomainNameVerifier);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::shouldBeStrict();

        Relation::enforceMorphMap([
            'Jobseeker' => JobSeekerProfile::class,
            'Employer' => EmployerProfile::class,
        ]);
    }
}
