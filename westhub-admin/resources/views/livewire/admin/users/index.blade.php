<div class="space-y-4" wire:init="loadData">
    <div class="glass-card p-5 flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-xs uppercase tracking-[0.2em] text-admin-muted">Administrative</p>
            <h2 class="inline-flex items-center gap-2 text-2xl font-semibold">
                <x-admin.icon name="team" class="h-5 w-5 text-primary-100" />
                User Management
            </h2>
        </div>
        <a href="{{ route('admin.users.create') }}" class="admin-primary-btn gap-2">
            <x-admin.icon name="plus" class="h-4 w-4" />
            <span>Add User</span>
        </a>
    </div>

    <div class="glass-card p-2">
        <div class="flex items-center gap-3 p-2">
            <div class="relative flex-1 max-w-[400px]">
                <x-admin.icon name="search" class="absolute left-3 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-admin-muted" />
                <input type="text" wire:model.live.debounce.300ms="search" class="admin-input !pl-9 !h-10" placeholder="Search by name or email...">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-white/5 text-admin-muted">
                    <tr>
                        <th class="text-left p-4">Name</th>
                        <th class="text-left p-4">Email</th>
                        <th class="text-left p-4">Roles</th>
                        <th class="text-left p-4">Joined</th>
                        <th class="text-right p-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-admin-stroke/50">
                    @forelse($users as $user)
                        <tr class="hover:bg-white/5 transition-colors">
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-full bg-primary-500/20 flex items-center justify-center text-primary-200 font-bold text-xs">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <span class="font-medium text-admin-ink">{{ $user->name }}</span>
                                </div>
                            </td>
                            <td class="p-4 text-admin-muted">{{ $user->email }}</td>
                            <td class="p-4">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($user->roles as $role)
                                        <span class="px-2 py-0.5 rounded text-[10px] bg-white/10 text-admin-ink border border-admin-stroke">
                                            {{ str($role->name)->replace('_', ' ')->title() }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="p-4 text-admin-muted">{{ $user->created_at->format('M d, Y') }}</td>
                            <td class="p-4 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="admin-icon-btn" title="Edit">
                                        <x-admin.icon name="edit" class="h-4 w-4" />
                                    </a>
                                    @if($user->id !== auth()->id())
                                        <button type="button" class="admin-icon-btn text-rose-400" wire:click="confirmDelete({{ $user->id }})" title="Delete">
                                            <x-admin.icon name="trash" class="h-4 w-4" />
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-12 text-center text-admin-muted">
                                @if($readyToLoad)
                                    No users found matching your criteria.
                                @else
                                    <div class="flex justify-center">
                                        <x-admin.icon name="refresh" class="h-6 w-6 animate-spin" />
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-admin-stroke/50">
            {{ $users->links() }}
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    @if($deletingUserId)
        <div class="admin-modal-backdrop" @click="$wire.set('deletingUserId', null)"></div>
        <div class="admin-modal-panel max-w-md">
            <h3 class="text-xl font-semibold">Delete User</h3>
            <p class="mt-2 text-sm text-admin-muted">Are you sure you want to delete this user? This action cannot be undone.</p>
            <div class="mt-4 flex justify-end gap-2">
                <button class="admin-ghost-btn" @click="$wire.set('deletingUserId', null)">Cancel</button>
                <button class="admin-primary-btn !bg-rose-500/20 !border-rose-500/50 !text-rose-300" wire:click="deleteUser">
                    Delete User
                </button>
            </div>
        </div>
    @endif
</div>
