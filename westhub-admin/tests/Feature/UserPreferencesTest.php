<?php

namespace Tests\Feature;

use App\Livewire\Admin\Articles\Index as ArticlesIndex;
use App\Models\AdminPreference;
use App\Models\ArticleCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UserPreferencesTest extends TestCase
{
    use RefreshDatabase;

    public function test_articles_preferences_are_saved_for_view_sort_status_and_category(): void
    {
        $user = User::factory()->create();
        $category = ArticleCategory::create([
            'name' => 'Guides',
            'is_active' => true,
        ]);

        $this->actingAs($user);

        Livewire::test(ArticlesIndex::class)
            ->call('setViewMode', 'grid')
            ->call('toggleSort', 'title')
            ->set('status', 'published')
            ->call('setCategory', $category->id);

        $pref = AdminPreference::query()
            ->where('user_id', $user->id)
            ->where('module', 'articles.index')
            ->first();

        $this->assertNotNull($pref);
        $this->assertSame('grid', $pref->preferences['view_mode'] ?? null);
        $this->assertSame('title', $pref->preferences['sort_by'] ?? null);
        $this->assertSame('desc', $pref->preferences['sort_direction'] ?? null);
        $this->assertSame('published', $pref->preferences['status'] ?? null);
        $this->assertSame($category->id, $pref->preferences['category_id'] ?? null);
    }

    public function test_articles_preferences_are_loaded_on_mount(): void
    {
        $user = User::factory()->create();
        $category = ArticleCategory::create([
            'name' => 'News',
            'is_active' => true,
        ]);

        AdminPreference::create([
            'user_id' => $user->id,
            'module' => 'articles.index',
            'preferences' => [
                'view_mode' => 'cards',
                'sort_by' => 'created_at',
                'sort_direction' => 'asc',
                'status' => 'scheduled',
                'category_id' => $category->id,
            ],
        ]);

        $this->actingAs($user);

        Livewire::test(ArticlesIndex::class)
            ->assertSet('viewMode', 'cards')
            ->assertSet('sortBy', 'created_at')
            ->assertSet('sortDirection', 'asc')
            ->assertSet('status', 'scheduled')
            ->assertSet('categoryId', $category->id);
    }
}
