<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>WestHub Admin Login</title>
    <script>
        (() => {
            const key = 'westhub-admin-theme';
            const saved = window.localStorage.getItem(key);
            const theme = (saved === 'light' || saved === 'dark')
                ? saved
                : (window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark');
            document.documentElement.dataset.theme = theme;
        })();
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Funnel+Display:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @if(! app()->environment('testing'))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="min-h-screen grid place-items-center p-6 font-sans" x-data="westhubTheme()" x-init="boot()">
    <div class="admin-ambient"></div>
    <div class="admin-topbar-controls fixed right-4 top-4 z-50">
        <button class="floating-icon-btn" type="button" @click="toggle()" title="Toggle theme">
            <x-admin.icon name="sun" class="h-4 w-4" x-show="theme === 'dark'" x-cloak />
            <x-admin.icon name="moon" class="h-4 w-4" x-show="theme === 'light'" x-cloak />
        </button>
    </div>
    <div class="glass-card relative w-full max-w-md p-6 md:p-8 lift-on-hover">
        <p class="text-[10px] md:text-xs uppercase tracking-[0.2em] text-admin-muted">WestHub</p>
        <h1 class="text-2xl md:text-3xl font-semibold mt-2">Admin Sign In</h1>
        <p class="mt-2 text-xs md:text-sm text-admin-muted">Secure access for editorial and operations team members.</p>

        <form
            class="mt-8 space-y-4"
            action="{{ route('login.store', [], false) }}"
            method="POST"
            x-data="westhubLoginForm('{{ route('login.store', [], false) }}')"
            @submit.prevent="submit($event)"
            novalidate
        >
            @csrf
            <div>
                <label class="admin-label" for="email">Email</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    required
                    autofocus
                    autocomplete="email"
                    inputmode="email"
                    maxlength="255"
                    class="admin-input"
                    x-model.trim="form.email"
                    @blur="validateEmail"
                    :class="{'is-valid': touched.email && !errors.email, 'is-invalid': touched.email && !!errors.email}"
                >
                <p class="admin-input-feedback is-error" x-show="errors.email" x-text="errors.email" x-cloak></p>
                @error('email')
                    <p class="admin-input-feedback is-error">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="admin-label" for="password">Password</label>
                <input
                    id="password"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    minlength="8"
                    maxlength="255"
                    class="admin-input"
                    x-model="form.password"
                    @blur="validatePassword"
                    :class="{'is-valid': touched.password && !errors.password, 'is-invalid': touched.password && !!errors.password}"
                >
                <p class="admin-input-feedback is-error" x-show="errors.password" x-text="errors.password" x-cloak></p>
                @error('password')
                    <p class="admin-input-feedback is-error">{{ $message }}</p>
                @enderror
            </div>
            <label class="admin-check-wrap">
                <input type="checkbox" name="remember" class="sr-only peer" x-model="form.remember">
                <span class="admin-check-box">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M5 12.5L9.2 16.5L19 7.5" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span class="text-sm text-admin-muted">Remember me on this device</span>
            </label>
            <div class="admin-request-feedback is-error" x-show="requestState === 'error' && serverFeedback" x-cloak>
                <p x-text="serverFeedback"></p>
            </div>
            <button class="admin-primary-btn w-full gap-2" type="submit" :disabled="loading">
                <span x-show="!loading">Sign in</span>
                <span x-show="loading" class="inline-flex items-center gap-2">
                    <span class="tiny-orb-loader"></span>
                    Authenticating...
                </span>
            </button>
            <template x-teleport="body">
                <div class="westhub-overlay-loader" x-show="loading" x-transition.opacity x-cloak>
                    <div class="westhub-loader-core">
                        <div class="westhub-loader-ring"></div>
                        <div class="westhub-loader-ring is-mid"></div>
                        <div class="westhub-loader-ring is-inner"></div>
                        <div class="westhub-loader-label">Verifying Access</div>
                    </div>
                </div>
            </template>
        </form>
    </div>
</body>
</html>
