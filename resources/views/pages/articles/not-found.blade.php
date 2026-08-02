<x-layouts.app :seo="$seo">
    <section class="py-20 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 text-center md:px-6">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-primary-100">Article unavailable</p>
            <h1 class="mt-4 text-4xl font-bold leading-tight text-primary-300 font-display md:text-5xl">
                We could not find that article.
            </h1>
            <p class="mx-auto mt-5 max-w-xl text-base leading-7 text-neutral-400">
                The article may have moved, or it may not be published yet.
            </p>
            <div class="mt-8 flex justify-center">
                <a href="{{ route('articles.index') }}" class="inline-flex items-center gap-2 rounded-full bg-primary-100 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-primary-100/20 transition-colors hover:bg-primary-200">
                    Browse Articles
                    <x-icon-arrow-right class="h-4 w-4" />
                </a>
            </div>
        </div>
    </section>
</x-layouts.app>
