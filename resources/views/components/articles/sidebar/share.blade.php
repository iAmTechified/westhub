@props(['article'])

@php
    $shareUrl = urlencode(request()->fullUrl());
    $shareTitle = urlencode($article->title);
@endphp

<section class="mt-4 rounded-2xl border border-neutral-200 bg-white p-6">
    <p class="text-xs font-medium text-neutral-400">Share</p>

    <div class="mt-3 space-y-3">
        <a
            href="https://twitter.com/intent/tweet?url={{ $shareUrl }}&text={{ $shareTitle }}"
            target="_blank"
            rel="noopener noreferrer"
            class="flex items-center gap-2 text-sm font-medium text-neutral-600 transition-colors hover:text-primary-100"
        >
            <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
            <span>X (Formerly Twitter)</span>
        </a>

        <a
            href="https://www.linkedin.com/sharing/share-offsite/?url={{ $shareUrl }}"
            target="_blank"
            rel="noopener noreferrer"
            class="flex items-center gap-2 text-sm font-medium text-neutral-600 transition-colors hover:text-primary-100"
        >
            <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.761 0 5-2.239 5-5v-14c0-2.761-2.239-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764S5.534 3.204 6.5 3.204s1.75.79 1.75 1.764S7.467 6.732 6.5 6.732zM20 19h-3v-5.604c0-3.368-4-3.113-4 0V19h-3V8h3v1.765c1.396-2.586 7-2.777 7 2.476V19z"/></svg>
            <span>LinkedIn</span>
        </a>

        <a
            href="mailto:?subject={{ $shareTitle }}&body={{ $shareUrl }}"
            class="flex items-center gap-2 text-sm font-medium text-neutral-600 transition-colors hover:text-primary-100"
        >
            <x-icon-mail class="h-3.5 w-3.5" />
            <span>Mail</span>
        </a>
    </div>
</section>
