<?php

namespace App\Livewire;

use App\Models\Article;
use App\Models\ArticleCategory;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ArticlesIndex extends Component
{
    use WithPagination;

    #[Url(as: 'category', history: true, except: 'all')]
    public string $category = 'all';

    public int $perPage = 6;

    public function mount(): void
    {
        $this->category = $this->normalizeCategorySlug($this->category);
    }

    public function setCategory(string $category): void
    {
        $this->category = $this->normalizeCategorySlug($category);
        $this->resetPage();
    }

    public function render()
    {
        $categories = $this->articleCategories();
        $selectedCategory = $this->selectedCategory($categories);

        $query = Article::query()
            ->with('category')
            ->publiclyVisible()
            ->orderByDesc('published_at')
            ->orderByDesc('id');

        if ($selectedCategory) {
            $query->where('article_category_id', $selectedCategory->id);
        }

        return view('livewire.articles-index', [
            'articles' => $query->paginate($this->perPage),
            'categories' => $categories,
            'selectedCategory' => $selectedCategory,
        ]);
    }

    protected function articleCategories(): Collection
    {
        return ArticleCategory::query()
            ->active()
            ->orderBy('name')
            ->get();
    }

    protected function selectedCategory(Collection $categories): ?ArticleCategory
    {
        if ($this->category === 'all') {
            return null;
        }

        $selectedCategory = $categories->firstWhere('slug', $this->category);

        if (! $selectedCategory) {
            $this->category = 'all';

            return null;
        }

        return $selectedCategory;
    }

    protected function normalizeCategorySlug(?string $category): string
    {
        $slug = Str::slug((string) $category);

        return in_array($slug, ['', 'all', 'view-all'], true) ? 'all' : $slug;
    }
}
