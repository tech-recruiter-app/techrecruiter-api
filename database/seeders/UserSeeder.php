<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

final class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $isVerified = (bool) random_int(0, 1);
            $factory = User::factory();

            $factory = match (random_int(0, 1)) {
                0 => $isVerified ? $factory->jobseeker() : $factory->jobseeker()->unverified(),
                1 => $isVerified ? $factory->employer() : $factory->employer()->unverified(),
            };

            $factory->create();
        }
    }
}
