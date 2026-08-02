<?php

namespace App\Livewire\Admin\Users;

use App\Livewire\Admin\Concerns\InteractsWithAdminToast;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;
use Spatie\Permission\Models\Permission;

class Studio extends Component
{
    use InteractsWithAdminToast;

    public ?User $user = null;
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public array $selectedPermissions = [];

    public function mount(?User $user = null): void
    {
        if ($user && $user->exists) {
            $this->user = $user;
            $this->name = $user->name;
            $this->email = $user->email;
            $this->selectedPermissions = $user->permissions->pluck('name')->toArray();
        }
    }

    public function save()
    {
        $isEdit = $this->user && $this->user->exists;

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email' . ($isEdit ? ',' . $this->user->id : '')],
            'selectedPermissions' => ['required', 'array'],
        ];

        if (! $isEdit || $this->password !== '') {
            $rules['password'] = ['required', 'confirmed', Password::defaults()];
        }

        $validated = $this->validate($rules);

        if ($isEdit) {
            $this->user->update([
                'name' => $this->name,
                'email' => $this->email,
            ]);

            if ($this->password !== '') {
                $this->user->update(['password' => Hash::make($this->password)]);
            }

            // Grant permissions directly to the user
            $this->user->syncPermissions($this->selectedPermissions);
            
            $this->toastSuccess('User updated successfully.', 'User Management');
        } else {
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
            ]);

            $user->syncPermissions($this->selectedPermissions);
            $this->toastSuccess('User created successfully.', 'User Management');
        }

        return redirect()->route('admin.users.index');
    }

    public function render()
    {
        // Get all permissions except access_users. 
        // User management is strictly for Super Admins.
        $permissions = Permission::where('name', '!=', 'access_users')->get();

        return view('livewire.admin.users.studio', compact('permissions'))
            ->layout('layouts.admin');
    }
}
