<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\AdminPermissions;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

/**
 * Create an admin account, or change an existing one's role.
 *
 * This is the supported way to grant admin access. The users table is shared
 * with the public site, so a person can exist there without being able to open
 * the admin; this is what gives them a role.
 */
class ManageAdminUser extends Command
{
    protected $signature = 'westhub:admin-user
        {email : The account email address}
        {--name= : Display name, used only when creating a new account}
        {--role=super_admin : One of super_admin, editor, reviewer, ops, marketing}
        {--password= : Set a password. Omit to generate one, or keep the existing password}
        {--list : Show every account and its role, then exit}';

    protected $description = 'Create an admin user or change their role';

    public function handle(): int
    {
        if ($this->option('list')) {
            return $this->listUsers();
        }

        $role = (string) $this->option('role');

        if (! in_array($role, AdminPermissions::roles(), true)) {
            $this->error('Unknown role "' . $role . '".');
            $this->line('Valid roles: ' . implode(', ', AdminPermissions::roles()));

            return self::FAILURE;
        }

        if (! \Spatie\Permission\Models\Role::where('name', $role)->exists()) {
            $this->error('The roles table is empty. Run this first:');
            $this->line('  php artisan db:seed --class=RolesAndPermissionsSeeder --force');

            return self::FAILURE;
        }

        $email = strtolower(trim((string) $this->argument('email')));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('"' . $email . '" is not a valid email address.');

            return self::FAILURE;
        }

        $user = User::query()->where('email', $email)->first();
        $password = (string) ($this->option('password') ?? '');
        $generated = null;

        if (! $user) {
            if ($password === '') {
                $generated = Str::password(16, symbols: false);
                $password = $generated;
            }

            $user = User::query()->create([
                'name' => (string) ($this->option('name') ?: Str::of($email)->before('@')->headline()),
                'email' => $email,
                'password' => Hash::make($password),
            ]);

            $this->info('Created account ' . $email);
        } else {
            if ($password !== '') {
                $user->forceFill(['password' => Hash::make($password)])->save();
                $this->info('Password updated for ' . $email);
            } else {
                $this->info('Account ' . $email . ' already exists; password left unchanged.');
            }
        }

        $previous = $user->getRoleNames()->implode(', ');
        $user->syncRoles([$role]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->info('Role: ' . ($previous !== '' ? $previous . ' -> ' : '') . $role);

        if ($generated !== null) {
            $this->newLine();
            $this->warn('Temporary password (shown once, change it after signing in):');
            $this->line('  ' . $generated);
            $this->newLine();
        }

        return self::SUCCESS;
    }

    protected function listUsers(): int
    {
        $rows = User::query()->orderBy('id')->get(['id', 'name', 'email'])
            ->map(fn (User $user): array => [
                $user->id,
                $user->name,
                $user->email,
                $user->getRoleNames()->implode(', ') ?: 'no admin role',
            ])
            ->all();

        if ($rows === []) {
            $this->warn('There are no user accounts. Create one with:');
            $this->line('  php artisan westhub:admin-user you@example.com --role=super_admin');

            return self::SUCCESS;
        }

        $this->table(['ID', 'Name', 'Email', 'Role'], $rows);

        return self::SUCCESS;
    }
}
