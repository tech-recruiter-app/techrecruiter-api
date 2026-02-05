<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Database\Seeder;

final class JobPostingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employers = User::employers()->get();

        foreach ($employers as $employer) {
            JobPosting::factory()->draft()->create(['employer_id' => $employer->id]);
            JobPosting::factory()->active()->create(['employer_id' => $employer->id]);
            JobPosting::factory()->closed()->create(['employer_id' => $employer->id]);
        }
    }
}
