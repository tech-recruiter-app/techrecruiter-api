<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\JobApplicationStatus;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Database\Seeder;

final class JobApplicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jobseekers = User::jobseekers()->get();
        $jobPostings = JobPosting::published()->get();

        foreach ($jobPostings as $jobPosting) {
            $n = random_int(2, 4);
            for ($i = 0; $i < $n; $i++) {
                JobApplication::factory()->withStatus($this->randomStatus())->create([
                    'job_posting_id' => $jobPosting->id,
                    'applicant_id' => $jobseekers->random()->id,
                ]);
            }
        }
    }

    private function randomStatus(): JobApplicationStatus
    {
        $statuses = JobApplicationStatus::cases();
        $randomKey = array_rand($statuses);
        assert(array_key_exists($randomKey, $statuses));

        return $statuses[$randomKey];
    }
}
