<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Article;

class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = ['Professional Careers', 'Family Care', 'Nursing Care'];
        
        // 12 items as per design grid (6 per page)
        for ($i = 0; $i < 12; $i++) {
            Article::create([
                'title' => 'Find the Professional Care Your Family Deserves.',
                'category' => $categories[$i % 3],
                'excerpt' => 'Choosing to bring a caregiver into your home is one of the most significant decisions a family can make. It is an act of love, a commitment to safety, and a pursuit of a higher quality of life...',
                'body' => 'Full clinical content for article ' . ($i + 1),
                'image_path' => null,
                'published_at' => now()->subDays($i),
            ]);
        }
    }
}
