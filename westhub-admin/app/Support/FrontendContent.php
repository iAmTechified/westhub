<?php

namespace App\Support;

use App\Models\GalleryItem;
use App\Models\Testimonial;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class FrontendContent
{
    /** @var array<string, bool> Per-request cache for Schema::hasTable checks. */
    private static array $tableExists = [];

    private const GALLERY_RATIOS = [
        'aspect-[3/4]',
        'aspect-[4/3]',
        'aspect-square',
        'aspect-[2/3]',
        'aspect-[16/9]',
        'aspect-[4/3]',
        'aspect-[3/4]',
        'aspect-square',
    ];

    public static function testimonials(): array
    {
        try {
            $testimonials = Testimonial::query()
                ->where('status', 'published')
                ->where(function ($query) {
                    $query->whereNull('published_at')
                        ->orWhere('published_at', '<=', now());
                })
                ->orderBy('sort_order')
                ->orderByDesc('published_at')
                ->orderBy('id')
                ->get();

            if ($testimonials->isEmpty()) {
                return self::fallbackTestimonials();
            }

            return $testimonials
                ->map(fn (Testimonial $testimonial): array => self::testimonialViewData([
                    'author_name' => $testimonial->author_name,
                    'author_role' => $testimonial->author_role,
                    'avatar_path' => $testimonial->avatar_path,
                    'quote' => $testimonial->quote,
                    'rating' => $testimonial->rating,
                ]))
                ->values()
                ->all();
        } catch (Throwable $exception) {
            report($exception);

            return self::fallbackTestimonials();
        }
    }

    public static function fallbackTestimonials(): array
    {
        return array_map(
            fn (array $testimonial): array => self::testimonialViewData($testimonial),
            self::fallbackTestimonialRows()
        );
    }

    public static function fallbackTestimonialRows(): array
    {
        return [
            [
                'author_name' => 'Liam Miller',
                'author_role' => 'Civil Engineer',
                'avatar_path' => 'assets/images/testimonials/Liam Miller.png',
                'quote' => "As someone who appreciates precision and systems, I was incredibly impressed by WestHub's coordination. Their post-hospital care plan was structured perfectly, allowing me to focus entirely on my recovery.",
                'rating' => 5,
            ],
            [
                'author_name' => 'Noah Anderson',
                'author_role' => 'Marketing Strategist',
                'avatar_path' => 'assets/images/testimonials/Noah Anderson.png',
                'quote' => "WestHub Healthcare understands the power of a personal touch. Their caregivers didn't just provide support; they built a real connection with my father, making a difficult transition feel seamless.",
                'rating' => 5,
            ],
            [
                'author_name' => 'Oliver Smith',
                'author_role' => 'High School Principal',
                'avatar_path' => 'assets/images/testimonials/Oliver Smith.png',
                'quote' => 'The level of professionalism WestHub brings to in-home care is exemplary. They treat their clients with the same patience and dedication I expect in a classroom setting. Truly top-tier service.',
                'rating' => 5,
            ],
            [
                'author_name' => 'Jameson Davis',
                'author_role' => 'Software Developer',
                'avatar_path' => 'assets/images/testimonials/Jameson Davis.png',
                'quote' => 'The flexibility of their hourly homemaker services is a lifesaver. Being able to schedule reliable care around my project deadlines has given me and my family much-needed peace of mind.',
                'rating' => 5,
            ],
            [
                'author_name' => 'Mateo Garcia',
                'author_role' => 'Financial Analyst',
                'avatar_path' => 'assets/images/testimonials/Mateo Garcia.png',
                'quote' => "When you look at the quality of care versus the cost, WestHub offers incredible value. Their CHAP accreditation gave me the data-backed confidence I needed to trust them with my mother's health.",
                'rating' => 4,
            ],
            [
                'author_name' => 'Theodore Wilson',
                'author_role' => 'Real Estate Broker',
                'avatar_path' => 'assets/images/testimonials/Theodore Wilson.png',
                'quote' => "In my business, location and comfort are everything. WestHub makes it possible for seniors to stay in the homes they love while receiving elite-level nursing care. I can't recommend them enough.",
                'rating' => 5,
            ],
            [
                'author_name' => 'Henry Thompson',
                'author_role' => 'Clinical Psychologist',
                'avatar_path' => 'assets/images/testimonials/Henry Thompson.png',
                'quote' => 'I am particularly impressed by their approach to dementia care. They prioritize the emotional and mental wellbeing of the patient, which is essential for maintaining dignity in long-term health management.',
                'rating' => 5,
            ],
            [
                'author_name' => 'Elijah Carter',
                'author_role' => 'Logistics Coordinator',
                'avatar_path' => 'assets/images/testimonials/Elijah Carter.png',
                'quote' => "The reliability of WestHub's live-in services is unmatched. They managed every detail of my uncle's daily routine with such efficiency that our entire family felt the weight lift off our shoulders.",
                'rating' => 5,
            ],
            [
                'author_name' => 'Olivia Johnson',
                'author_role' => 'Registered Nurse',
                'avatar_path' => 'assets/images/testimonials/Olivia Johnson.png',
                'quote' => 'Coming from a medical background, I have very high standards for clinical care. WestHub Healthcare exceeded them all. Their nursing staff is competent, compassionate, and incredibly thorough.',
                'rating' => 5,
            ],
            [
                'author_name' => 'Emma Williams',
                'author_role' => 'Interior Designer',
                'avatar_path' => 'assets/images/testimonials/Emma Williams.png',
                'quote' => 'I love that WestHub respects the home environment. Their homemakers are discreet, helpful, and keep things tidy, ensuring the living space remains a sanctuary while providing essential care.',
                'rating' => 5,
            ],
            [
                'author_name' => 'Amelia Jones',
                'author_role' => 'Data Scientist',
                'avatar_path' => 'assets/images/testimonials/Amelia Jones.png',
                'quote' => "I did my research before choosing an agency, and WestHub's track record stood out. The personalized care plan they generated for my grandmother was detailed, logical, and highly effective.",
                'rating' => 5,
            ],
            [
                'author_name' => 'Sophia Martinez',
                'author_role' => 'Human Resources Manager',
                'avatar_path' => 'assets/images/testimonials/Sophia Martinez.png',
                'quote' => 'The staffing quality at WestHub is excellent. You can tell they vet their caregivers carefully; everyone we worked with was professional, punctual, and genuinely kind-hearted.',
                'rating' => 5,
            ],
            [
                'author_name' => 'Charlotte Brown',
                'author_role' => 'Environmental Consultant',
                'avatar_path' => 'assets/images/testimonials/Charlotte Brown.png',
                'quote' => "I appreciate WestHub's sustainable approach to long-term wellness. They don't just fix immediate problems; they create a healthy, supportive environment that fosters genuine independence for seniors.",
                'rating' => 5,
            ],
            [
                'author_name' => 'Aurora Taylor',
                'author_role' => 'Creative Director',
                'avatar_path' => 'assets/images/testimonials/Aurora Taylor.png',
                'quote' => 'WestHub Healthcare brings a beautiful sense of humanity to home nursing. They helped us design a care schedule that fit our aesthetic and lifestyle, making the medical aspects of care feel less intrusive.',
                'rating' => 5,
            ],
        ];
    }

    public static function galleryImages(?int $limit = null): Collection
    {
        try {
            $items = GalleryItem::query()
                ->where('status', 'published')
                ->where('visibility', 'public')
                ->where(function ($query) {
                    $query->whereNull('published_at')
                        ->orWhere('published_at', '<=', now());
                })
                ->orderBy('sort_order')
                ->orderByDesc('published_at')
                ->orderBy('id')
                ->when($limit, fn ($query) => $query->limit($limit))
                ->get(['id', 'title', 'alt_text', 'caption']);

            if ($items->isEmpty()) {
                return self::fallbackGalleryImages($limit);
            }

            $media = self::galleryMediaFor($items->pluck('id')->all());
            $images = $items
                ->map(function (GalleryItem $item, int $index) use ($media): ?array {
                    return self::galleryItemViewData($item, $index, $media->get($item->id));
                })
                ->filter()
                ->values();

            return $images->isNotEmpty() ? $images : self::fallbackGalleryImages($limit);
        } catch (Throwable $exception) {
            report($exception);

            return self::fallbackGalleryImages($limit);
        }
    }

    public static function homeGalleryImages(): Collection
    {
        return self::galleryImages(10);
    }

    public static function fallbackGalleryImages(?int $limit = null): Collection
    {
        $files = collect(self::fallbackGalleryFileNames())
            ->filter(fn (string $file): bool => File::exists(public_path('assets/images/gallery/'.$file)))
            ->when($limit, fn (Collection $collection) => $collection->take($limit))
            ->values();

        return $files->map(fn (string $file, int $index): array => [
            'url' => asset('assets/images/gallery/'.$file),
            'alt' => self::galleryAltText($file),
            'title' => self::galleryAltText($file),
            'caption' => null,
            'ratio' => self::GALLERY_RATIOS[$index % count(self::GALLERY_RATIOS)],
            'file_name' => $file,
        ]);
    }

    public static function fallbackGalleryFileNames(): array
    {
        return [
            'old man & grand daughter.jpg',
            'senior-couple-holding-hands 1.jpg',
            'medium-shot-health-worker-helping-patient 1.jpg',
            'full-shot-woman-wheelchair 1.jpg',
            'Scaled down - side-view-smiley-nurse-talking-patient 1.png',
            'joyful-old-lady-sitting-couch-nursing-home-holding-health-taker-arm 1.jpg',
            'medium-shot-friend-surprising-woman 1.jpg',
            'old-man-standing-gray-backround-with-his-granddaughter 1.png',
            'Scaled - women-holding-hands-medium-shot 1.png',
            'senior-man-nursing-home-with-dumbbells-doing-physiotherapy-with-help-from-nurse 1.jpg',
            'Slide Image 1.jpg',
            'Slide Image 2.jpg',
            'Slide Image 3.jpg',
        ];
    }

    private static function testimonialViewData(array $testimonial): array
    {
        return [
            'name' => $testimonial['author_name'],
            'role' => $testimonial['author_role'] ?? '',
            'avatar' => self::assetUrl($testimonial['avatar_path'] ?? null),
            'text' => $testimonial['quote'],
            'stars' => max(0, min(5, (int) ($testimonial['rating'] ?? 5))),
        ];
    }

    private static function galleryMediaFor(array $itemIds): Collection
    {
        if ($itemIds === []) {
            return collect();
        }

        $connection = config('database.content_connection', 'content');

        if (! self::tableExists($connection, 'media')) {
            return collect();
        }

        return DB::connection($connection)
            ->table('media')
            ->where('model_type', GalleryItem::class)
            ->where('collection_name', 'gallery')
            ->whereIn('model_id', $itemIds)
            ->orderBy('order_column')
            ->orderBy('id')
            ->get()
            ->groupBy('model_id')
            ->map(fn (Collection $files) => $files->first());
    }

    private static function galleryItemViewData(GalleryItem $item, int $index, ?object $media): ?array
    {
        $url = null;

        if ($media && self::mediaFileExists($media)) {
            $url = self::mediaUrl($media);
        }

        if (! $url) {
            $fallbackFile = self::fallbackGalleryFileForTitle($item->title);

            if ($fallbackFile) {
                $url = asset('assets/images/gallery/'.$fallbackFile);
            }
        }


        if (! $url) {
            return null;
        }

        return [
            'url' => $url,
            'alt' => $item->alt_text ?: $item->title,
            'title' => $item->title,
            'caption' => $item->caption,
            'ratio' => self::GALLERY_RATIOS[$index % count(self::GALLERY_RATIOS)],
        ];
    }


    private static function mediaFileExists(object $media): bool
    {
        $path = $media->id.'/'.$media->file_name;

        try {
            if ($media->disk && config("filesystems.disks.{$media->disk}")) {
                $disk = config("filesystems.disks.{$media->disk}");

                if (($disk['driver'] ?? null) !== 'local') {
                    return true;
                }

                return Storage::disk($media->disk)->exists($path);
            }
        } catch (Throwable) {
            //
        }

        return File::exists(public_path('storage/'.$path));
    }

    private static function mediaUrl(object $media): string
    {
        $path = $media->id.'/'.$media->file_name;

        try {
            if ($media->disk && config("filesystems.disks.{$media->disk}")) {
                return Storage::disk($media->disk)->url($path);
            }
        } catch (Throwable) {
            //
        }

        return asset('storage/'.$path);
    }

    private static function fallbackGalleryFileForTitle(?string $title): ?string
    {
        if (! $title) {
            return null;
        }

        static $filesByTitle = null;

        if ($filesByTitle === null) {
            $filesByTitle = [];

            foreach (self::fallbackGalleryFileNames() as $file) {
                if (! File::exists(public_path('assets/images/gallery/'.$file))) {
                    continue;
                }

                $filesByTitle[Str::slug(self::galleryAltText($file))] = $file;
            }
        }

        return $filesByTitle[Str::slug($title)] ?? null;
    }

    private static function assetUrl(?string $path): string
    {
        if (! $path) {
            return asset('assets/images/testimonials/Liam Miller.png');
        }

        if (Str::startsWith($path, ['http://', 'https://', '/'])) {
            return $path;
        }

        return asset($path);
    }

    private static function galleryAltText(string $file): string
    {
        return Str::of(pathinfo($file, PATHINFO_FILENAME))
            ->replace(['-', '_'], ' ')
            ->squish()
            ->title()
            ->toString();
    }

    /**
     * Check if a DB table exists, caching the result per-request to avoid
     * repeated information_schema queries within the same request cycle.
     */
    private static function tableExists(string $connection, string $table): bool
    {
        $key = "{$connection}.{$table}";

        if (! array_key_exists($key, self::$tableExists)) {
            self::$tableExists[$key] = Schema::connection($connection)->hasTable($table);
        }

        return self::$tableExists[$key];
    }
}
