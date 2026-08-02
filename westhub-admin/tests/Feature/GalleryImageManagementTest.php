<?php

namespace Tests\Feature;

use App\Livewire\Admin\Gallery\Index as GalleryIndex;
use App\Models\GalleryItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GalleryImageManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_gallery_item_replacement_keeps_single_media_and_updates_file(): void
    {
        Storage::fake('public');

        Role::findOrCreate('super_admin');
        $user = User::factory()->create();
        $user->assignRole('super_admin');
        $this->actingAs($user);

        Livewire::test(GalleryIndex::class)
            ->set('title', 'Kitchen')
            ->set('itemUpload', UploadedFile::fake()->image('kitchen.jpg', 1000, 700))
            ->call('saveItem');

        $item = GalleryItem::query()->firstOrFail();
        $firstMedia = $item->getFirstMedia('gallery');
        $this->assertNotNull($firstMedia);
        $firstPath = $firstMedia->getPathRelativeToRoot();
        Storage::disk('public')->assertExists($firstPath);

        Livewire::test(GalleryIndex::class)
            ->call('openEditModal', $item->id)
            ->set('title', 'Kitchen Updated')
            ->set('itemUpload', UploadedFile::fake()->image('kitchen-new.png', 1000, 700))
            ->call('saveItem');

        $item->refresh();
        $item->load('media');
        $secondMedia = $item->getFirstMedia('gallery');
        $this->assertNotNull($secondMedia);
        $this->assertCount(1, $item->media);
        $secondPath = $secondMedia->getPathRelativeToRoot();

        $this->assertNotSame($firstPath, $secondPath);
        Storage::disk('public')->assertMissing($firstPath);
        Storage::disk('public')->assertExists($secondPath);
    }
}

