<div class="space-y-6" wire:init="loadData">
    <header class="glass-card p-6 lg:p-8 lift-on-hover">
        <p class="text-xs uppercase tracking-[0.22em] text-admin-muted">Overview</p>
        <h2 class="mt-2 inline-flex items-center gap-2 text-3xl font-semibold">
            <x-admin.icon name="dashboard" class="h-6 w-6 text-primary-100" />
            Executive Dashboard
        </h2>
        <p class="mt-2 text-admin-muted">Welcome back, {{ $user?->name ?? 'Admin' }}. Real-time pulse across publishing, recruitment, and media operations.</p>
    </header>

    <section class="grid gap-4 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
        @php($kpi = [
            ['label' => 'Published Articles', 'key' => 'published_articles', 'icon' => 'article', 'permission' => 'articles.view'],
            ['label' => 'Pending Applications', 'key' => 'pending_applications', 'icon' => 'briefcase', 'permission' => 'join_requests.view'],
            ['label' => 'Pending Appointments', 'key' => 'pending_appointments', 'icon' => 'calendar', 'permission' => 'appointments.view'],
            ['label' => 'Scheduled Articles', 'key' => 'scheduled_articles', 'icon' => 'sort', 'permission' => 'articles.view'],
            ['label' => 'Published Gallery', 'key' => 'published_gallery_items', 'icon' => 'image', 'permission' => 'gallery.view'],
            ['label' => 'Active Services', 'key' => 'active_services', 'icon' => 'pulse', 'permission' => 'care_services.view'],
        ])
        @foreach($kpi as $card)
            @can($card['permission'])
            <div class="glass-card p-5 lift-on-hover">
                <p class="admin-kpi-label inline-flex items-center gap-1.5">
                    <x-admin.icon :name="$card['icon']" class="h-3.5 w-3.5" />
                    {{ $card['label'] }}
                </p>
                @if(! $readyToLoad)
                    <div class="admin-skeleton mt-3 h-10 w-20"></div>
                @else
                    <p class="admin-kpi-value">{{ $metrics[$card['key']] }}</p>
                @endif
            </div>
            @endcan
        @endforeach
    </section>

    <section class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        @if(count($quickLinks) > 0)
        <div class="glass-card p-5">
            <h3 class="text-lg font-semibold">Quick Links</h3>
            <div class="mt-4 grid gap-2">
                @foreach($quickLinks as $link)
                    <a class="admin-row-item" href="{{ $link['route'] }}">
                        <span class="inline-flex items-center gap-2">
                            <x-admin.icon :name="$link['icon']" class="h-4 w-4 text-primary-100" />
                            {{ $link['label'] }}
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
        @endif

        @if(count($quickActions) > 0)
        <div class="glass-card p-5">
            <h3 class="text-lg font-semibold">Quick Actions</h3>
            <div class="mt-4 grid gap-2">
                @foreach($quickActions as $action)
                    <a class="admin-primary-btn w-full justify-start gap-2" href="{{ $action['route'] }}">
                        <x-admin.icon :name="$action['icon']" class="h-4 w-4" />
                        {{ $action['label'] }}
                    </a>
                @endforeach
            </div>
        </div>
        @endif

        @can('articles.view')
        <div class="glass-card p-5">
            <h3 class="text-lg font-semibold">Recent Article Activity</h3>
            <div class="mt-4 space-y-2">
                @if(! $readyToLoad)
                    <div class="admin-skeleton h-10"></div>
                    <div class="admin-skeleton h-10"></div>
                    <div class="admin-skeleton h-10"></div>
                @else
                    @forelse($recentArticles as $article)
                        <a href="{{ route('admin.articles.edit', $article) }}" class="admin-row-item">
                            <span>{{ $article->title }}</span>
                            <span class="text-xs text-admin-muted">{{ $article->status }}</span>
                        </a>
                    @empty
                        <p class="text-admin-muted">No article activity yet.</p>
                    @endforelse
                @endif
            </div>
        </div>
        @endcan
    </section>

    @can('join_requests.view')
    <section class="grid gap-4 xl:grid-cols-1">
        <div class="glass-card p-5">
            <h3 class="text-lg font-semibold">Recent Applications</h3>
            <div class="mt-4 space-y-2">
                @if(! $readyToLoad)
                    <div class="admin-skeleton h-10"></div>
                    <div class="admin-skeleton h-10"></div>
                    <div class="admin-skeleton h-10"></div>
                @else
                    @forelse($recentJoinRequests as $request)
                        <a href="{{ route('admin.join-requests.index') }}" class="admin-row-item">
                            <span>{{ $request->full_name }}</span>
                            <span class="text-xs text-admin-muted">{{ $request->status }}</span>
                        </a>
                    @empty
                        <p class="text-admin-muted">No applications yet.</p>
                    @endforelse
                @endif
            </div>
        </div>
    </section>
    @endcan
</div>
