<?php

namespace App\Livewire\Admin\Users;

use App\Livewire\Admin\Concerns\InteractsWithAdminToast;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
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
        if ($userId === Auth::id()) {
            $this->toastError('You cannot delete yourself.', 'User Management');
            return;
        }

        $this->deletingUserId = $userId;
    }

    public function deleteUser(): void
    {
        if (! $this->deletingUserId) {
            return;
        }

        $user = User::find($this->deletingUserId);
        if ($user) {
            $user->delete();
            $this->toastSuccess('User deleted successfully.', 'User Management');
        }

        $this->deletingUserId = null;
    }

    public function render()
    {
        $users = User::query()
            ->when($this->search !== '', function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(12);

        return view('livewire.admin.users.index', compact('users'))
            ->layout('layouts.admin');
    }
}
