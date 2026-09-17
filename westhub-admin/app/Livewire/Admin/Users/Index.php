<?php

namespace App\Livewire\Admin\Users;

use App\Livewire\Admin\Concerns\InteractsWithAdminToast;
use App\Models\User;
use App\Support\AdminPermissions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;
    use InteractsWithAdminToast;

    public string $search = '';
    public ?int $deletingUserId = null;
    public bool $readyToLoad = false;

    protected $paginationView = 'livewire.admin-pagination';

    public function mount(): void
    {
        Gate::authorize('users.view');
    }

    public function loadData(): void
    {
        $this->readyToLoad = true;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function confirmDelete(int $userId): void
    {
        Gate::authorize('users.manage');

        if ($reason = $this->deletionBlockedReason($userId)) {
            $this->toastError($reason, 'User Management');

            return;
        }

        $this->deletingUserId = $userId;
    }

    public function deleteUser(): void
    {
        Gate::authorize('users.manage');

        if (! $this->deletingUserId) {
            return;
        }

        // deletingUserId is a public property the browser can set directly, so
        // every safety check must be repeated here, not only in confirmDelete.
        if ($reason = $this->deletionBlockedReason($this->deletingUserId)) {
            $this->toastError($reason, 'User Management');
            $this->deletingUserId = null;

            return;
        }

        $user = User::find($this->deletingUserId);

        if ($user) {
            $user->delete();
            $this->toastSuccess('User deleted successfully.', 'User Management');
        }

        $this->deletingUserId = null;
    }

    protected function deletionBlockedReason(int $userId): ?string
    {
        if ($userId === Auth::id()) {
            return 'You cannot delete your own account.';
        }

        $target = User::find($userId);

        if (! $target) {
            return null;
        }

        if ($target->hasRole(AdminPermissions::SUPER_ADMIN)) {
            if (! Auth::user()->hasRole(AdminPermissions::SUPER_ADMIN)) {
                return 'Only a super admin can delete another super admin.';
            }

            $remaining = User::role(AdminPermissions::SUPER_ADMIN)->count();

            if ($remaining <= 1) {
                return 'You cannot delete the last super admin, or nobody could manage users.';
            }
        }

        return null;
    }

    public function render()
    {
        $users = User::query()
            ->with('roles')
            ->when($this->search !== '', function ($query) {
                $query->where(function ($inner) {
                    $inner->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->latest()
            ->paginate(12);

        return view('livewire.admin.users.index', [
            'users' => $users,
            'roleLabels' => collect(AdminPermissions::roleDescriptions())->map(fn (array $role): string => $role['label'])->all(),
        ])->layout('layouts.admin');
    }
}
