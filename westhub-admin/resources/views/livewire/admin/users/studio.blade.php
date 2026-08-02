<div class="max-w-4xl mx-auto space-y-6">
    <div class="glass-card p-5 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3 md:gap-4">
            <a href="{{ route('admin.users.index') }}" class="admin-icon-btn h-9 w-9 md:h-10 md:w-10">
                <x-admin.icon name="chevron-left" class="h-4 w-4 md:h-5 md:w-5" />
            </a>
            <div>
                <p class="text-[10px] md:text-xs uppercase tracking-[0.2em] text-admin-muted">User Management</p>
                <h2 class="text-xl md:text-2xl font-semibold">{{ $user ? 'Edit User' : 'Add New User' }}</h2>
            </div>
        </div>
        <button type="button" class="admin-primary-btn gap-2 h-10 px-4 text-sm md:text-base" wire:click="save">
            <x-admin.icon name="save" class="h-4 w-4" />
            <span>{{ $user ? 'Update' : 'Create' }} <span class="hidden sm:inline">User</span></span>
        </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="glass-card p-6 space-y-6">
                <h3 class="text-lg font-semibold border-b border-admin-stroke/50 pb-3">Basic Information</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-admin-ink">Full Name</label>
                        <input type="text" wire:model="name" class="admin-input" placeholder="John Doe">
                        @error('name') <p class="text-xs text-rose-300">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium text-admin-ink">Email Address</label>
                        <input type="email" wire:model="email" class="admin-input" placeholder="john@example.com">
                        @error('email') <p class="text-xs text-rose-300">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="glass-card p-6 space-y-6">
                <h3 class="text-lg font-semibold border-b border-admin-stroke/50 pb-3">Security</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-admin-ink">Password</label>
                        <input type="password" wire:model="password" class="admin-input" placeholder="{{ $user ? 'Leave blank to keep current' : '••••••••' }}">
                        @error('password') <p class="text-xs text-rose-300">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium text-admin-ink">Confirm Password</label>
                        <input type="password" wire:model="password_confirmation" class="admin-input" placeholder="{{ $user ? 'Leave blank to keep current' : '••••••••' }}">
                    </div>
                </div>

                @if($user)
                    <div class="p-4 bg-white/5 rounded-lg border border-admin-stroke/50">
                        <p class="text-xs text-admin-muted">
                            <x-admin.icon name="lock" class="inline h-3 w-3 mr-1" />
                            To change this user's password, provide a new one above. Otherwise, the existing password will remain.
                        </p>
                    </div>
                @endif
            </div>
        </div>

        <div class="space-y-6">
            <div class="glass-card p-6 space-y-6">
                <h3 class="text-lg font-semibold border-b border-admin-stroke/50 pb-3">Access Control</h3>
                
                <div class="space-y-4">
                    <label class="text-sm font-medium text-admin-ink">Feature Access</label>
                    <div class="space-y-2">
                        @foreach($permissions as $permission)
                            <label class="flex items-center gap-3 p-3 rounded-lg border border-admin-stroke/50 hover:bg-white/5 cursor-pointer transition-colors">
                                <input type="checkbox" wire:model="selectedPermissions" value="{{ $permission->name }}" class="h-4 w-4 rounded border-admin-stroke bg-admin-surface text-primary-500 focus:ring-primary-500/20">
                                <div>
                                    <p class="text-sm font-medium text-admin-ink">
                                        {{ str($permission->name)->replace('access_', '')->replace('_', ' ')->title() }}
                                    </p>
                                    <p class="text-[11px] text-admin-muted">
                                        @if($permission->name === 'access_articles') Manage blog posts, news, and categories.
                                        @elseif($permission->name === 'access_applications') Review recruitment applications and join requests.
                                        @elseif($permission->name === 'access_subscribers') Manage newsletter subscribers and mailing lists.
                                        @elseif($permission->name === 'access_appointments') Manage clinical bookings and appointments.
                                        @elseif($permission->name === 'access_gallery') Manage media albums and gallery items.
                                        @elseif($permission->name === 'access_care_services') Configure care services and service groups.
                                        @elseif($permission->name === 'access_locations') Manage operational locations and counties.
                                        @elseif($permission->name === 'access_users') Create and manage administrative users.
                                        @elseif($permission->name === 'access_settings') Manage global site settings and configurations.
                                        @else Grant access to this feature.
                                        @endif
                                    </p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    @error('selectedPermissions') <p class="text-xs text-rose-300">{{ $message }}</p> @enderror
                </div>
            </div>

            @if($user)
                <div class="glass-card p-6 space-y-4">
                    <h3 class="text-sm font-semibold text-admin-muted uppercase tracking-wider">Account Metadata</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between text-xs">
                            <span class="text-admin-muted">Last Active</span>
                            <span class="text-admin-ink">Never</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-admin-muted">Created</span>
                            <span class="text-admin-ink">{{ $user->created_at->format('M d, Y') }}</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-admin-muted">ID</span>
                            <span class="text-admin-ink">#{{ $user->id }}</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
