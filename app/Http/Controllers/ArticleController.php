<?php

namespace App\Http\Controllers;

use App\Models\Article;

class ArticleController extends Controller
{
    /**
     * Display a listing of the articles.
     */
    public function index()
    {
        $seo = [
            'title' => 'Health & Wellness Insights | WestHub Healthcare Articles',
            'description' => 'Explore expert home healthcare advice, nursing tips, and family care insights from WestHub Healthcare. CHAP-certified excellence in Illinois.',
        ];

        return view('pages.articles', compact('seo'));
    }

    /**
     * Display the specified article.
     */
    public function show($slug)
    {
        $article = Article::query()
            ->with('category')
            ->where('slug', $slug)
            ->first();

        if (! $article || ! $article->isPubliclyVisible()) {
            $seo = [
                'title' => 'Article not found | WestHub Healthcare',
                'description' => 'The article you are looking for is not available.',
            ];

            return response()->view('pages.articles.not-found', compact('seo'), 404);
        }

        $relatedArticles = Article::query()
            ->with('category')
            ->publiclyVisible()
            ->whereKeyNot($article->id)
            ->when($article->article_category_id, function ($query) use ($article) {
                $query->where('article_category_id', $article->article_category_id);
            })
            ->orderByDesc('published_at')
            ->limit(6)
            ->get();

        if ($relatedArticles->count() < 6) {
            $relatedArticles = $relatedArticles->concat(
                Article::query()
                    ->with('category')
                    ->publiclyVisible()
                    ->whereKeyNot($relatedArticles->pluck('id')->push($article->id)->all())
                    ->orderByDesc('published_at')
                    ->limit(6 - $relatedArticles->count())
                    ->get()
            );
        }

        $seo = [
            'title' => ($article->seo_title ?: $article->title).' | WestHub Healthcare',
            'description' => $article->seo_description ?: $article->excerpt,
            'og_image' => $article->ogImageUrl(),
        ];

        return view('pages.article-show', compact('article', 'relatedArticles', 'seo'));
    }
}
