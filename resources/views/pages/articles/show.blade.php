<x-layouts.app :seo="$seo">
    {{-- Hero Section --}}
    <x-articles.hero :article="$article" />

    {{-- Content Area --}}
    <div class="container px-5 md:px-6 mx-auto mt-12 mb-24 xl:px-20">
        <div class="flex flex-col xl:flex-row xl:space-x-16">
            {{-- Main Content Column --}}
            <div class="xl:w-2/3">
                <x-articles.body :article="$article" />
            </div>

            {{-- Sidebar Column --}}
            <aside class="mt-12 xl:mt-0 xl:w-1/3">
                <div class="xl:sticky xl:top-24">
                    <x-articles.sidebar.author :article="$article" />
                    <x-articles.sidebar.share />
                    <x-articles.sidebar.related :relatedArticles="$relatedArticles" />
                </div>
            </aside>
        </div>
    </div>
    
    <x-newsletter />

</x-layouts.app>
