<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminPassword = env('SEED_ADMIN_PASSWORD');

        if (is_string($adminPassword) && trim($adminPassword) !== '') {
            $admin = User::query()->firstOrNew([
                'email' => env('SEED_ADMIN_EMAIL', 'admin@example.com'),
            ]);

            $admin->forceFill([
                'name' => env('SEED_ADMIN_NAME', 'Admin User'),
                'password' => $adminPassword,
                'email_verified_at' => $admin->email_verified_at ?? now(),
            ])->save();
        }

        $this->call([
            LocationSeeder::class,
            TestimonialSeeder::class,
        ]);

    }
}
