@props(['article'])

<article class="flex flex-col overflow-hidden transition-all duration-500 bg-white group rounded-[2.5rem] hover:shadow-2xl hover:shadow-primary-100/10 hover:-translate-y-1.5">
    <!-- Image -->
    <div class="relative w-full aspect-[4/3] overflow-hidden p-3 pb-0">
        <div class="w-full h-full overflow-hidden rounded-[2rem]">
            <img
                src="{{ $article->imageUrl() }}"
                alt="{{ $article->title }}"
                class="object-cover w-full h-full transition-transform duration-700 group-hover:scale-105"
            >
        </div>
    </div>

    <!-- Content -->
    <div class="flex flex-col flex-1 p-8 pt-6 md:p-10 md:pt-8">
        <h3 class="mb-4 text-2xl font-bold leading-tight transition-colors text-primary-300 group-hover:text-primary-200 font-display">
            <a href="{{ route('articles.show', $article->slug) }}" class="after:absolute after:inset-0">
                {{ $article->title }}
            </a>
        </h3>
        
        <p class="text-sm leading-relaxed text-neutral-400 line-clamp-3 font-sans">
            {{ $article->excerpt }}
        </p>
    </div>
</article>
