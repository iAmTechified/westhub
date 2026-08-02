<?php

namespace Tests\Feature;

use App\Livewire\Admin\Articles\Studio;
use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ArticleImageManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_headline_image_upload_persists_and_replacement_is_atomic(): void
    {
        Storage::fake('public');

        Role::findOrCreate('super_admin');
        $user = User::factory()->create();
        $user->assignRole('super_admin');
        $this->actingAs($user);

        $studio = Livewire::test(Studio::class)
            ->set('title', 'Image Workflow')
            ->set('headlineImageUpload', UploadedFile::fake()->image('hero.jpg', 1200, 800))
            ->call('saveDraft')
            ->assertSet('status', Article::STATUS_DRAFT);

        $articleId = $studio->get('article.id');
        $article = Article::query()->findOrFail($articleId);
        $firstPath = (string) $article->headline_image_path;

        $this->assertNotSame('', $firstPath);
        Storage::disk('public')->assertExists($firstPath);

        Livewire::test(Studio::class, ['article' => $article])
            ->set('title', $article->title)
            ->set('headlineImageUpload', UploadedFile::fake()->image('hero-new.png', 1200, 800))
            ->call('saveDraft');

        $article->refresh();
        $secondPath = (string) $article->headline_image_path;

        $this->assertNotSame($firstPath, $secondPath);
        Storage::disk('public')->assertMissing($firstPath);
        Storage::disk('public')->assertExists($secondPath);
        $this->assertStringStartsWith('articles/headlines/', $secondPath);
        $this->assertStringStartsWith('/storage/', (string) $article->headlineImageUrl());
    }
}

