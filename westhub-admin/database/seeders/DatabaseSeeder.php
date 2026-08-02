<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Define Feature Permissions
        $permissions = [
            'access_articles',
            'access_applications',
            'access_subscribers',
            'access_appointments',
            'access_gallery',
            'access_care_services',
            'access_locations',
            'access_users',
            'access_settings',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // Only super_admin is explicitly defined as a role
        Role::findOrCreate('super_admin');

        $admin = User::query()->firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@westhub.local')],
            [
                'name' => 'WestHub Admin',
                'password' => Hash::make(env('ADMIN_PASSWORD', 'password')),
            ]
        );

        $admin->assignRole('super_admin');

        $this->call([
            LocationSeeder::class,
            TestimonialSeeder::class,
            JoinRequestSeeder::class,
            AppointmentSeeder::class,
            CareServicesSeeder::class,
            GallerySeeder::class,
        ]);
    }
}
