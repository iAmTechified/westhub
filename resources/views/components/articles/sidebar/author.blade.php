@props(['article'])

<section class="rounded-2xl bg-[#E6E6E6] p-6">
    <p class="text-xs font-medium text-neutral-400">Author</p>

    <div class="mt-3 flex items-center gap-3">
        <div class="relative flex-shrink-0">
            <img
                src="{{ $article->authorImageUrl() }}"
                alt="{{ $article->authorDisplayName() }}"
                class="h-9 w-9 rounded-full object-cover ring-2 ring-white shadow-sm"
            >
        </div>
        <div class="min-w-0">
            <h2 class="text-[17px] font-semibold leading-tight text-neutral-500 font-display">{{ $article->authorDisplayName() }}</h2>
            <p class="text-xs leading-tight text-neutral-400 font-sans">{{ $article->authorDisplayRole() }}</p>
        </div>
    </div>

    <p class="mt-4 text-[13px] leading-[1.3] text-neutral-500 font-sans">
        {{ $article->authorDisplayBio() }}
    </p>
</section>
