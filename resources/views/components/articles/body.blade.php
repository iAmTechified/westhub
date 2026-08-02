@props(['article'])

<div class="prose prose-neutral max-w-none prose-headings:font-display prose-headings:text-neutral-600 prose-p:text-neutral-500 prose-p:leading-relaxed prose-li:text-neutral-500 lg:prose-lg prose-h2:text-3xl lg:prose-h2:text-4xl prose-h2:mt-12">
    {!! $article->renderedBody() !!}
</div>
