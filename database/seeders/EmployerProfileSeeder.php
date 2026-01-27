<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\EmployerProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

final class EmployerProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employers = User::employers()->get();

        foreach ($employers as $employer) {
            $profile = EmployerProfile::factory()->create();
            $employer->profile()->associate($profile);
            $employer->save();
        }
    }
}
