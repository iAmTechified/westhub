<?php

namespace Database\Seeders;

use App\Models\GalleryCategory;
use App\Models\GalleryItem;
use App\Support\FrontendContent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GallerySeeder extends Seeder
{
    public function run(): void
    {
        $category = GalleryCategory::query()->firstOrCreate(
            ['slug' => 'care-moments'],
            [
                'name' => 'Care Moments',
                'description' => 'WestHub Healthcare team and client care moments.',
                'is_active' => true,
            ]
        );

        foreach (FrontendContent::fallbackGalleryFileNames() as $index => $fileName) {
            $path = public_path('assets/images/gallery/'.$fileName);

            if (! File::exists($path)) {
                continue;
            }

            $title = Str::of(pathinfo($fileName, PATHINFO_FILENAME))
                ->replace(['-', '_'], ' ')
                ->squish()
                ->title()
                ->toString();

            $item = GalleryItem::query()->firstOrCreate(
                ['slug' => Str::slug($title)],
                [
                    'gallery_category_id' => $category->id,
                    'title' => $title,
                    'alt_text' => $title,
                    'caption' => null,
                    'visibility' => 'public',
                    'status' => 'published',
                    'sort_order' => $index + 1,
                    'published_at' => now(),
                ]
            );

            if (! $item->getFirstMedia('gallery')) {
                $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION)) ?: 'jpg';

                $item->addMedia($path)
                    ->preservingOriginal()
                    ->usingName($title)
                    ->usingFileName(Str::slug($title).'.'.$extension)
                    ->toMediaCollection('gallery');
            }
        }
    }
}
