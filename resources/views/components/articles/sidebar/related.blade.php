@props(['relatedArticles'])

<section class="mt-4 rounded-2xl border border-neutral-200 bg-white p-6">
    <p class="text-xs font-medium text-neutral-400">Related Articles</p>

    <div class="mt-4 space-y-4">
        @foreach($relatedArticles as $related)
            <a href="{{ route('articles.show', $related->slug) }}" class="grid grid-cols-[102px_minmax(0,1fr)] gap-3 group">
                <div class="relative overflow-hidden rounded-md">
                    <img
                        src="{{ $related->imageUrl() }}"
                        alt="{{ $related->title }}"
                        class="h-[67px] w-[102px] object-cover transition-transform duration-500 group-hover:scale-105"
                    >
                </div>
                <div class="min-w-0">
                    <h4 class="text-[13px] font-semibold leading-[1.15] text-neutral-600 transition-colors group-hover:text-primary-100 line-clamp-2 font-display">
                        {{ $related->title }}
                    </h4>
                    <p class="mt-1 text-[11px] leading-tight text-neutral-300 font-sans uppercase tracking-wider">{{ $related->category_name }}</p>
                </div>
            </a>
        @endforeach
    </div>
</section>
