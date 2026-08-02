<div
    x-data="{
        toasts: [],
        maxToasts: 6,
        now: Date.now(),
        clock: null,
        init() {
            this.clock = window.setInterval(() => {
                this.now = Date.now();
            }, 120);
        },
        destroy() {
            if (this.clock) {
                window.clearInterval(this.clock);
            }
            this.clearAll();
        },
        normalizeType(type) {
            const tone = String(type ?? 'info').toLowerCase().trim();
            if (tone === 'success') return 'success';
            if (tone === 'error' || tone === 'danger') return 'error';
            if (tone === 'warning' || tone === 'warn') return 'warning';
            return 'info';
        },
        defaultTitle(type) {
            if (type === 'success') return 'Success';
            if (type === 'error') return 'Error';
            if (type === 'warning') return 'Warning';
            return 'Info';
        },
        clampDuration(value) {
            const duration = Number(value);
            if (!Number.isFinite(duration)) return 4500;
            return Math.max(1800, Math.min(12000, duration));
        },
        push(payload = {}) {
            const message = String(payload?.message ?? '').trim();
            if (!message) return;

            const type = this.normalizeType(payload?.type);
            const duration = this.clampDuration(payload?.duration);
            const title = String(payload?.title ?? '').trim() || this.defaultTitle(type);
            const id = payload?.id ?? `${Date.now()}-${Math.random().toString(16).slice(2, 8)}`;

            const toast = {
                id,
                type,
                title,
                message,
                duration,
                remaining: duration,
                startedAt: 0,
                pausedAt: 0,
                isPaused: false,
                timeoutId: null,
            };

            this.toasts.unshift(toast);

            if (this.toasts.length > this.maxToasts) {
                const stale = this.toasts.pop();
                if (stale?.timeoutId) {
                    window.clearTimeout(stale.timeoutId);
                }
            }

            this.$nextTick(() => this.startTimer(id));
        },
        startTimer(id) {
            const toast = this.toasts.find((entry) => entry.id === id);
            if (!toast) return;

            if (toast.timeoutId) {
                window.clearTimeout(toast.timeoutId);
            }

            toast.startedAt = Date.now();
            toast.pausedAt = 0;
            toast.isPaused = false;

            toast.timeoutId = window.setTimeout(() => {
                this.dismiss(id);
            }, toast.remaining);
        },
        pause(id) {
            const toast = this.toasts.find((entry) => entry.id === id);
            if (!toast || toast.isPaused) return;

            if (toast.timeoutId) {
                window.clearTimeout(toast.timeoutId);
                toast.timeoutId = null;
            }

            const elapsed = Date.now() - toast.startedAt;
            toast.remaining = Math.max(0, toast.remaining - elapsed);
            toast.pausedAt = Date.now();
            toast.isPaused = true;
        },
        resume(id) {
            const toast = this.toasts.find((entry) => entry.id === id);
            if (!toast || !toast.isPaused) return;
            this.startTimer(id);
        },
        dismiss(id) {
            const toast = this.toasts.find((entry) => entry.id === id);
            if (toast?.timeoutId) {
                window.clearTimeout(toast.timeoutId);
            }

            this.toasts = this.toasts.filter((entry) => entry.id !== id);
        },
        clearAll() {
            this.toasts.forEach((toast) => {
                if (toast.timeoutId) {
                    window.clearTimeout(toast.timeoutId);
                }
            });
            this.toasts = [];
        },
        progressPercent(toast) {
            if (toast.isPaused) {
                return Math.max(0, Math.min(100, (toast.remaining / toast.duration) * 100));
            }

            const elapsed = this.now - toast.startedAt;
            const remaining = Math.max(0, toast.remaining - elapsed);
            return Math.max(0, Math.min(100, (remaining / toast.duration) * 100));
        },
        toneClass(type) {
            if (type === 'success') return 'is-success';
            if (type === 'error') return 'is-error';
            if (type === 'warning') return 'is-warning';
            return 'is-info';
        },
    }"
    @admin-toast.window="push($event.detail)"
    class="admin-toast-root"
    aria-live="polite"
    aria-atomic="true"
>
    <div class="admin-toast-stack">
        <div class="flex justify-end" x-show="toasts.length > 1" x-cloak>
            <button type="button" class="admin-toast-clear" @click="clearAll()">Clear all</button>
        </div>

        <template x-for="toast in toasts" :key="toast.id">
            <article
                x-show="true"
                x-transition:enter="transition ease-out duration-220"
                x-transition:enter-start="opacity-0 translate-y-2 translate-x-3 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 translate-x-0 scale-100"
                x-transition:leave="transition ease-in duration-160"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 translate-x-2 scale-95"
                class="admin-toast-item"
                :class="toneClass(toast.type)"
                @mouseenter="pause(toast.id)"
                @mouseleave="resume(toast.id)"
                role="status"
            >
                <div class="admin-toast-icon-wrap" aria-hidden="true">
                    <template x-if="toast.type === 'success'">
                        <x-admin.icon name="check" class="h-4 w-4" />
                    </template>
                    <template x-if="toast.type === 'error'">
                        <x-admin.icon name="x" class="h-4 w-4" />
                    </template>
                    <template x-if="toast.type === 'warning'">
                        <x-admin.icon name="info" class="h-4 w-4" />
                    </template>
                    <template x-if="toast.type === 'info'">
                        <x-admin.icon name="info" class="h-4 w-4" />
                    </template>
                </div>

                <div class="min-w-0 flex-1">
                    <p class="admin-toast-title" x-text="toast.title"></p>
                    <p class="admin-toast-message" x-text="toast.message"></p>
                </div>

                <button type="button" class="admin-toast-close" @click="dismiss(toast.id)" aria-label="Dismiss notification">
                    <x-admin.icon name="close" class="h-4 w-4" />
                </button>

                <div class="admin-toast-progress">
                    <div class="admin-toast-progress-bar" :style="`width: ${progressPercent(toast)}%`"></div>
                </div>
            </article>
        </template>
    </div>
</div>
