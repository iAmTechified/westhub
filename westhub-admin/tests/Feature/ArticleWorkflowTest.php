<?php

namespace Tests\Feature;

use App\Livewire\Admin\Articles\Studio;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ArticleWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_article_can_be_saved_as_draft_and_published(): void
    {
        Role::findOrCreate('super_admin');
        $user = User::factory()->create();
        $user->assignRole('super_admin');
        $category = ArticleCategory::create(['name' => 'Family Care', 'is_active' => true]);

        $this->actingAs($user);

        Livewire::test(Studio::class)
            ->set('title', 'A Test Article')
            ->set('excerpt', 'Excerpt')
            ->set('body', '<p>Body</p>')
            ->set('article_category_id', $category->id)
            ->call('saveDraft')
            ->assertSet('status', Article::STATUS_DRAFT)
            ->call('publish')
            ->assertSet('status', Article::STATUS_PUBLISHED);

        $this->assertDatabaseHas('articles', [
            'title' => 'A Test Article',
            'status' => Article::STATUS_PUBLISHED,
        ]);
    }
}
