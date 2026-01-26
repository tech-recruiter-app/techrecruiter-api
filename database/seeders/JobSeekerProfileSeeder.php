<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\JobSeekerProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

final class JobSeekerProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jobseekers = User::jobseekers()->get();

        foreach ($jobseekers as $jobseeker) {
            $profile = JobSeekerProfile::factory()->create();
            $jobseeker->profile()->associate($profile);
            $jobseeker->save();
        }
    }
}
