<x-layouts.app :seo="$seo">
    @php
        $shareUrl = urlencode(request()->fullUrl());
        $shareTitle = urlencode($article->title);
        $renderedBody = trim($article->renderedBody());
    @endphp

    <article class="pt-8 pb-16 lg:pt-9 lg:pb-20">
        <div class="mx-auto max-w-[1120px] px-4 md:px-6">
            <div class="grid gap-10 lg:grid-cols-[minmax(0,760px)_258px] lg:items-start lg:gap-16">
                <div class="min-w-0">
                    <div class="aspect-[752/527] w-full overflow-hidden rounded-[1.75rem] bg-primary-50">
                        <img
                            src="{{ $article->imageUrl() }}"
                            alt="{{ $article->title }}"
                            class="h-full w-full object-cover"
                        >
                    </div>

                    <header class="mt-7">
                        <h1 class="max-w-[740px] text-4xl font-bold leading-[1.08] text-neutral-600 font-display md:text-5xl lg:text-[46px]">
                            {{ $article->title }}
                        </h1>

                        <div class="mt-4 flex flex-wrap items-center gap-2 text-sm font-medium text-neutral-500">
                            @if($article->published_at)
                                <time datetime="{{ $article->published_at->toDateString() }}">
                                    {{ $article->published_at->format('jS F, Y') }}
                                </time>
                            @endif
                            <span class="h-2.5 w-2.5 rounded-full bg-neutral-300" aria-hidden="true"></span>
                            <span>{{ $article->category_name }}</span>
                        </div>
                    </header>

                    <div class="article-read-body mt-8">
                        @if($renderedBody !== '')
                            {!! $renderedBody !!}
                        @elseif($article->excerpt)
                            <p>{{ $article->excerpt }}</p>
                        @endif
                    </div>
                </div>

                <aside class="lg:sticky lg:top-24">
                    <x-articles.sidebar.author :article="$article" />
                    
                    <x-articles.sidebar.share :article="$article" />

                    @if($relatedArticles->isNotEmpty())
                        <x-articles.sidebar.related :relatedArticles="$relatedArticles" />
                    @endif
                </aside>
            </div>
        </div>
    </article>
</x-layouts.app>
