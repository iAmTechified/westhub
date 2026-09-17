<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>WestHub Admin</title>
    <script>
        (() => {
            const key = 'westhub-admin-theme';
            const saved = window.localStorage.getItem(key);
            const theme = (saved === 'light' || saved === 'dark')
                ? saved
                : 'light';
            document.documentElement.dataset.theme = theme;
        })();

        window.westhubTheme = window.westhubTheme || (() => ({
            theme: document.documentElement.dataset.theme || 'light',
            boot() {
                this.apply();
            },
            toggle() {
                this.theme = this.theme === 'dark' ? 'light' : 'dark';
                this.apply();
            },
            apply() {
                document.documentElement.dataset.theme = this.theme;
                window.localStorage.setItem('westhub-admin-theme', this.theme);
            },
        }));
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Funnel+Display:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @if(! app()->environment('testing'))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    @livewireStyles
</head>
<body class="font-sans min-h-screen antialiased" x-data="{ ...westhubTheme(), sidebarOpen: false }" x-init="boot()">
    <div class="admin-ambient"></div>
    <div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-[90] bg-black/60 backdrop-blur-sm lg:hidden" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>
    <div class="relative min-h-screen grid lg:h-screen lg:grid-cols-[260px_1fr] lg:overflow-hidden">
        <aside 
            class="glass-sidebar fixed inset-y-0 left-0 z-[100] w-[260px] border-r border-admin-stroke p-5 transform transition-transform duration-300 lg:sticky lg:translate-x-0 lg:z-10 lg:top-0 lg:h-screen lg:overflow-y-auto lg:p-6"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        >
            <div class="mb-6">
                <p class="text-xs uppercase tracking-[0.22em] text-admin-muted">WestHub</p>
                <h1 class="text-2xl font-semibold text-admin-ink">Admin</h1>
            </div>

            <nav class="space-y-1">
                @can('dashboard.view')
                <a href="{{ route('admin.dashboard') }}" class="admin-nav-link {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}">
                    <x-admin.icon name="dashboard" class="h-4 w-4" />
                    Dashboard
                </a>
                @endcan
                @can('articles.view')
                <a href="{{ route('admin.articles.index') }}" class="admin-nav-link {{ request()->routeIs('admin.articles.*') ? 'is-active' : '' }}">
                    <x-admin.icon name="article" class="h-4 w-4" />
                    Articles
                </a>
                @endcan
                @can('join_requests.view')
                <a href="{{ route('admin.join-requests.index') }}" class="admin-nav-link {{ request()->routeIs('admin.join-requests.*') ? 'is-active' : '' }}">
                    <x-admin.icon name="briefcase" class="h-4 w-4" />
                    Applications
                </a>
                @endcan
                @can('subscribers.view')
                <a href="{{ route('admin.subscribers.index') }}" class="admin-nav-link {{ request()->routeIs('admin.subscribers.*') ? 'is-active' : '' }}">
                    <x-admin.icon name="mail" class="h-4 w-4" />
                    Subscribers
                </a>
                @endcan
                @can('appointments.view')
                <a href="{{ route('admin.appointments.index') }}" class="admin-nav-link {{ request()->routeIs('admin.appointments.*') ? 'is-active' : '' }}">
                    <x-admin.icon name="calendar" class="h-4 w-4" />
                    Appointments
                </a>
                @endcan
                @can('promos.view')
                <a href="{{ route('admin.promo-claims.index') }}" class="admin-nav-link {{ request()->routeIs('admin.promo-claims.*') ? 'is-active' : '' }}">
                    <x-admin.icon name="gift" class="h-4 w-4" />
                    Promo Claims
                </a>
                @endcan
                @can('gallery.view')
                <a href="{{ route('admin.gallery.index') }}" class="admin-nav-link {{ request()->routeIs('admin.gallery.*') ? 'is-active' : '' }}">
                    <x-admin.icon name="image" class="h-4 w-4" />
                    Gallery
                </a>
                @endcan
                @can('care_services.view')
                <a href="{{ route('admin.care-services.index') }}" class="admin-nav-link {{ request()->routeIs('admin.care-services.*') ? 'is-active' : '' }}">
                    <x-admin.icon name="pulse" class="h-4 w-4" />
                    Care Services
                </a>
                @endcan
                @can('locations.view')
                <a href="{{ route('admin.locations.index') }}" class="admin-nav-link {{ request()->routeIs('admin.locations.*') ? 'is-active' : '' }}">
                    <x-admin.icon name="location" class="h-4 w-4" />
                    Locations
                </a>
                @endcan
                @can('users.view')
                <a href="{{ route('admin.users.index') }}" class="admin-nav-link {{ request()->routeIs('admin.users.*') ? 'is-active' : '' }}">
                    <x-admin.icon name="team" class="h-4 w-4" />
                    Users
                </a>
                @endcan
                <a href="{{ route('admin.settings.index') }}" class="admin-nav-link {{ request()->routeIs('admin.settings.*') ? 'is-active' : '' }}">
                    <x-admin.icon name="settings" class="h-4 w-4" />
                    Settings
                </a>
            </nav>

            <form action="{{ route('admin.logout') }}" method="POST" class="mt-8">
                @csrf
                <button class="w-full admin-ghost-btn gap-2" type="submit">
                    <x-admin.icon name="logout" class="h-4 w-4" />
                    Sign out
                </button>
            </form>
        </aside>

        <div class="relative z-10 min-h-screen flex flex-col lg:h-screen lg:overflow-y-auto">
            <header class="admin-topbar">
                <div class="flex items-center justify-between w-full">
                    <div class="flex items-center gap-3">
                        <button class="lg:hidden admin-icon-btn !rounded-full !h-10 !w-10 shadow-lg bg-admin-surface/80 backdrop-blur-md border-admin-stroke" @click="sidebarOpen = true">
                            <x-admin.icon name="menu" class="h-5 w-5" />
                        </button>
                        <a href="{{ route('admin.dashboard') }}" class="admin-icon-btn !rounded-full !h-10 !w-10 bg-admin-surface/40 backdrop-blur-md border-admin-stroke hidden sm:flex items-center justify-center" title="Dashboard Home">
                            <x-admin.icon name="grid" class="h-4 w-4" />
                        </a>
                    </div>
                    <div class="admin-topbar-controls">
                        <button class="floating-icon-btn" type="button" @click="toggle()" title="Toggle theme">
                            <x-admin.icon name="sun" class="h-4 w-4" x-show="theme === 'dark'" x-cloak />
                            <x-admin.icon name="moon" class="h-4 w-4" x-show="theme === 'light'" x-cloak />
                        </button>
                    <div class="relative" x-data="{ open: false }">
                        <button class="floating-icon-btn" type="button" @click="open = !open" @click.outside="open = false">
                            <x-admin.icon name="profile" class="h-4 w-4" />
                        </button>
                        <div x-show="open" class="absolute right-0 top-full mt-2 w-48 max-w-[calc(100vw-2rem)] glass-card p-2 shadow-xl z-[110]" x-cloak>
                            <a href="{{ route('admin.settings.index') }}" class="admin-dropdown-item">
                                <x-admin.icon name="settings" class="h-4 w-4" />
                                Settings
                            </a>
                            <form action="{{ route('admin.logout') }}" method="POST">
                                @csrf
                                <button class="admin-dropdown-item w-full" type="submit">
                                    <x-admin.icon name="logout" class="h-4 w-4" />
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>
            <main class="p-4 md:p-6 lg:p-8">
                {{ $slot }}
            </main>
        </div>
    </div>

    @livewireScripts
    <x-admin.toast />
</body>
</html>
