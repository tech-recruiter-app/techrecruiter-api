<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\EmployerProfile;
use App\Models\JobSeekerProfile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Override;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    #[Override]
    public function register(): void
    {
        //
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
