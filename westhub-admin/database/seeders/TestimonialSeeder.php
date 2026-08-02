<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use App\Support\FrontendContent;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        foreach (FrontendContent::fallbackTestimonialRows() as $index => $testimonial) {
            Testimonial::query()->firstOrCreate(
                ['author_name' => $testimonial['author_name']],
                [
                    'author_role' => $testimonial['author_role'],
                    'quote' => $testimonial['quote'],
                    'rating' => $testimonial['rating'],
                    'avatar_path' => $testimonial['avatar_path'],
                    'status' => 'published',
                    'is_featured' => true,
                    'sort_order' => $index + 1,
                    'published_at' => now(),
                ]
            );
        }
    }
}
