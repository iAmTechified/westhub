<?php

namespace App\Livewire\Admin\Users;

use App\Livewire\Admin\Concerns\InteractsWithAdminToast;
use App\Models\User;
use App\Support\AdminPermissions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;
use Spatie\Permission\PermissionRegistrar;

/**
 * Create or edit an admin account.
 *
 * Access is granted by choosing ONE role. Individual permissions are no longer
 * ticked per person: the role decides what they can do, and AdminPermissions
 * is the single place that defines each role.
 */
class Studio extends Component
{
    use InteractsWithAdminToast;

    public ?User $user = null;
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $role = '';

    public function mount(?User $user = null): void
    {
        Gate::authorize('users.manage');

        if ($user && $user->exists) {
            $this->user = $user;
            $this->name = $user->name;
            $this->email = $user->email;
            $this->role = (string) ($user->getRoleNames()->first() ?? '');
        }
    }

    public function save()
    {
        Gate::authorize('users.manage');

        $isEdit = $this->user && $this->user->exists;

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email' . ($isEdit ? ',' . $this->user->id : '')],
            'role' => ['required', 'string', Rule::in($this->assignableRoles())],
        ];

        if (! $isEdit || $this->password !== '') {
            $rules['password'] = ['required', 'confirmed', Password::defaults()];
        }

        $this->validate($rules, [
            'role.required' => 'Choose a role. It decides what this person can open.',
            'role.in' => 'You cannot assign that role.',
        ]);

        if ($isEdit && ($reason = $this->roleChangeBlockedReason($this->user, $this->role))) {
            $this->addError('role', $reason);

            return null;
        }

        if ($isEdit) {
            $this->user->update([
                'name' => $this->name,
                'email' => $this->email,
            ]);

            if ($this->password !== '') {
                $this->user->update(['password' => Hash::make($this->password)]);
            }

            $target = $this->user;
            $message = 'User updated successfully.';
        } else {
            $target = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
            ]);

            $message = 'User created successfully.';
        }

        $target->syncRoles([$this->role]);
        // Clear any leftover direct grants from the retired access_* scheme.
        $target->syncPermissions([]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->toastSuccess($message, 'User Management');

        return redirect()->route('admin.users.index');
    }

    /**
     * Only a super admin may hand out the super_admin role.
     *
     * @return array<int, string>
     */
    protected function assignableRoles(): array
    {
        $roles = AdminPermissions::roles();

        if (! Auth::user()->hasRole(AdminPermissions::SUPER_ADMIN)) {
            $roles = array_values(array_diff($roles, [AdminPermissions::SUPER_ADMIN]));
        }

        return $roles;
    }

    protected function roleChangeBlockedReason(User $target, string $newRole): ?string
    {
        $wasSuperAdmin = $target->hasRole(AdminPermissions::SUPER_ADMIN);

        if (! $wasSuperAdmin || $newRole === AdminPermissions::SUPER_ADMIN) {
            return null;
        }

        if ($target->is(Auth::user())) {
            return 'You cannot remove your own super admin role.';
        }

        if (User::role(AdminPermissions::SUPER_ADMIN)->count() <= 1) {
            return 'This is the last super admin. Make someone else a super admin first.';
        }

        return null;
    }

    public function render()
    {
        $descriptions = AdminPermissions::roleDescriptions();
        $labels = AdminPermissions::all();

        $roles = collect($this->assignableRoles())->map(function (string $role) use ($descriptions, $labels): array {
            $grants = $role === AdminPermissions::SUPER_ADMIN
                ? ['Everything in the admin']
                : collect(AdminPermissions::roleMatrix()[$role] ?? [])->map(fn (string $permission): string => $labels[$permission] ?? $permission)->all();

            return [
                'name' => $role,
                'label' => $descriptions[$role]['label'] ?? $role,
                'summary' => $descriptions[$role]['summary'] ?? '',
                'grants' => $grants,
            ];
        })->all();

        return view('livewire.admin.users.studio', compact('roles'))
            ->layout('layouts.admin');
    }
}
