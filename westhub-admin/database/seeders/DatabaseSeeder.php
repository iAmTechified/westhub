<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\AdminPermissions;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Roles and permissions must exist before the admin user is given one.
        $this->call(RolesAndPermissionsSeeder::class);

        $admin = User::query()->firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@westhub.local')],
            [
                'name' => 'WestHub Admin',
                'password' => Hash::make(env('ADMIN_PASSWORD', 'password')),
            ]
        );

        $admin->assignRole(AdminPermissions::SUPER_ADMIN);

        $this->call([
            SettingsSeeder::class,
            LocationSeeder::class,
            TestimonialSeeder::class,
            JoinRequestSeeder::class,
            AppointmentSeeder::class,
            CareServicesSeeder::class,
            GallerySeeder::class,
        ]);
    }
}
