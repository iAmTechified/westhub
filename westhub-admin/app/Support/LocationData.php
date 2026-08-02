<?php

namespace App\Support;

use App\Models\County;
use App\Models\Location;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Throwable;

class LocationData
{
    /** @var array<string, bool> Per-request cache for Schema::hasTable checks. */
    private static array $schemaTableCache = [];
    /**
     * Get database-backed counties and townships, falling back to codebase data.
     * Cached for 1 hour to avoid repeated DB queries on every page/nav render.
     */
    public static function all(): array
    {
        return Cache::remember('location_data_all_counties', 3600, function (): array {
            $locations = self::databaseCounties();

            return $locations !== [] ? $locations : self::codebaseCounties();
        });
    }

    public static function countyLinks(): array
    {
        return collect(self::all())
            ->map(fn (array $county, string $slug): array => [
                'name' => $county['name'],
                'slug' => $slug,
            ])
            ->values()
            ->all();
    }

    /**
     * Get the flat Service Locations list from backend data, with codebase fallback.
     */
    public static function serviceLocationLinks(): array
    {
        $locations = self::databaseServiceLocationLinks();

        return $locations !== [] ? $locations : self::codebaseServiceLocationLinks();
    }

    /**
     * Canonical codebase location data used by seeders.
     */
    public static function seedableCounties(): array
    {
        return self::fallbackCounties(includeSortOrder: true);
    }

    public static function countyNamesForSentence(): string
    {
        $names = collect(self::countyLinks())
            ->pluck('name')
            ->filter()
            ->values()
            ->all();

        if ($names === []) {
            return 'our Illinois service areas';
        }

        if (count($names) === 1) {
            return $names[0];
        }

        $last = array_pop($names);

        return implode(', ', $names).', and '.$last;
    }

    /**
     * Get data for a specific county by slug.
     */
    public static function get(string $slug): ?array
    {
        return self::all()[$slug] ?? null;
    }

    public static function townshipsForCounty(string $countySlug): array
    {
        return self::get($countySlug)['townships'] ?? [];
    }

    /**
     * Get data for a specific township, preferring backend/database content.
     */
    public static function getTownship(string $countySlug, string $townshipSlug): ?object
    {
        return self::databaseTownship($countySlug, $townshipSlug)
            ?? self::legacyLocationTownship($countySlug, $townshipSlug)
            ?? self::codebaseTownship($countySlug, $townshipSlug);
    }

    public static function normalizeTownshipList(array $townships): array
    {
        return collect($townships)
            ->map(function ($township): ?array {
                $name = '';
                $slug = '';

                if (is_array($township)) {
                    $name = trim((string) ($township['name'] ?? ''));
                    $slug = trim((string) ($township['slug'] ?? ''));
                } elseif (is_object($township)) {
                    $name = trim((string) ($township->name ?? ''));
                    $slug = trim((string) ($township->slug ?? ''));
                } else {
                    $name = trim((string) $township);
                }

                if ($name === '') {
                    return null;
                }

                return [
                    'name' => $name,
                    'slug' => $slug !== '' ? $slug : str($name)->slug()->toString(),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    private static function databaseCounties(): array
    {
        try {
            $schema = self::contentSchema();

            if (! self::schemaHasTable($schema, 'counties') || ! self::schemaHasTable($schema, 'townships')) {
                return [];
            }

            $countyColumns = $schema->getColumnListing('counties');
            $townshipColumns = $schema->getColumnListing('townships');

            $query = County::query()
                ->with(['townships' => function ($query) use ($townshipColumns): void {
                    self::activeScope($query, $townshipColumns);
                    self::locationOrder($query, $townshipColumns);
                }]);

            self::activeScope($query, $countyColumns);
            self::locationOrder($query, $countyColumns);

            $counties = $query->get();

            if ($counties->isEmpty()) {
                return [];
            }

            $fallbackCounties = self::codebaseCounties();
            $hasDatabaseTownships = false;
            $locations = [];

            foreach ($counties as $county) {
                $name = trim((string) $county->name);
                $slug = trim((string) ($county->slug ?: str($name)->slug()->toString()));

                if ($name === '' || $slug === '') {
                    continue;
                }

                $fallbackCounty = $fallbackCounties[$slug] ?? [];
                $townships = self::normalizeTownshipList($county->townships?->all() ?? []);

                if ($townships !== []) {
                    $hasDatabaseTownships = true;
                } else {
                    $townships = $fallbackCounty['townships'] ?? [];
                }

                $description = trim((string) ($county->description ?? ''));

                $locations[$slug] = [
                    'name' => $name,
                    'coords' => $fallbackCounty['coords'] ?? self::defaultCoords(),
                    'subtitle' => $description !== ''
                        ? $description
                        : ($fallbackCounty['subtitle'] ?? 'Skilled Nurses, Caregivers & Home Healthcare Aides'),
                    'townships' => self::normalizeTownshipList($townships),
                    'meta_title' => $county->meta_title ?: ($fallbackCounty['meta_title'] ?? null),
                    'meta_description' => $county->meta_description ?: ($fallbackCounty['meta_description'] ?? null),
                ];
            }

            if ($locations === [] || ! $hasDatabaseTownships) {
                return [];
            }

            return $locations;
        } catch (Throwable) {
            return [];
        }
    }

    private static function databaseServiceLocationLinks(): array
    {
        try {
            $schema = self::contentSchema();

            if (! self::schemaHasTable($schema, 'counties')) {
                return [];
            }

            $countyColumns = $schema->getColumnListing('counties');
            $hasTownships = self::schemaHasTable($schema, 'townships');
            $townshipColumns = $hasTownships ? $schema->getColumnListing('townships') : [];

            $query = County::query();

            if ($hasTownships) {
                $query->with(['townships' => function ($query) use ($townshipColumns): void {
                    self::activeScope($query, $townshipColumns);
                    self::locationOrder($query, $townshipColumns);
                }]);
            }

            self::activeScope($query, $countyColumns);
            self::locationOrder($query, $countyColumns);

            $counties = $query->get();

            if ($counties->isEmpty()) {
                return [];
            }

            $locations = [];

            foreach ($counties as $county) {
                $countyName = trim((string) $county->name);
                $countySlug = trim((string) ($county->slug ?: str($countyName)->slug()->toString()));

                if ($countyName === '' || $countySlug === '') {
                    continue;
                }

                $locations[] = [
                    'name' => $countyName,
                    'href' => route('locations.county', ['county' => $countySlug]),
                    'sort_order' => (int) ($county->sort_order ?? 0),
                ];

                foreach (($county->townships ?? []) as $township) {
                    $townshipName = trim((string) $township->name);
                    $townshipSlug = trim((string) ($township->slug ?: str($townshipName)->slug()->toString()));

                    if ($townshipName === '' || $townshipSlug === '') {
                        continue;
                    }

                    $locations[] = [
                        'name' => $townshipName,
                        'href' => route('locations.township', [
                            'county' => $countySlug,
                            'township' => $townshipSlug,
                        ]),
                        'sort_order' => (int) ($township->sort_order ?? 0),
                    ];
                }
            }

            return self::dedupeServiceLocationLinks($locations);
        } catch (Throwable) {
            return [];
        }
    }

    private static function databaseTownship(string $countySlug, string $townshipSlug): ?object
    {
        try {
            $schema = self::contentSchema();

            if (! self::schemaHasTable($schema, 'counties') || ! self::schemaHasTable($schema, 'townships')) {
                return null;
            }

            $countyColumns = $schema->getColumnListing('counties');
            $townshipColumns = $schema->getColumnListing('townships');

            $countyQuery = County::query()->where('slug', $countySlug);
            self::activeScope($countyQuery, $countyColumns);

            $county = $countyQuery->first();

            if (! $county) {
                return null;
            }

            $townshipQuery = $county->townships()->where('slug', $townshipSlug);
            self::activeScope($townshipQuery, $townshipColumns);

            $township = $townshipQuery->first();

            if (! $township) {
                return null;
            }

            $townshipsQuery = $county->townships();
            self::activeScope($townshipsQuery, $townshipColumns);
            self::locationOrder($townshipsQuery, $townshipColumns);
            $townships = $townshipsQuery->get()->all();

            return self::makeTownshipPage(
                countyName: $county->name,
                townshipName: $township->name,
                countySlug: $countySlug,
                townshipSlug: $townshipSlug,
                content: is_array($township->content) ? $township->content : [],
                metaTitle: $township->meta_title,
                metaDescription: $township->meta_description,
                townships: self::normalizeTownshipList($townships),
            );
        } catch (Throwable) {
            return null;
        }
    }

    private static function legacyLocationTownship(string $countySlug, string $townshipSlug): ?object
    {
        try {
            $connection = (new Location())->getConnectionName() ?: config('database.default');
            $schema = Schema::connection($connection);

            if (! self::schemaHasTable($schema, 'locations')) {
                return null;
            }

            $location = Location::query()
                ->where('county_slug', $countySlug)
                ->where('township_slug', $townshipSlug)
                ->first();

            if (! $location) {
                return null;
            }

            return self::makeTownshipPage(
                countyName: $location->county_name,
                townshipName: $location->township_name,
                countySlug: $countySlug,
                townshipSlug: $townshipSlug,
                content: is_array($location->content) ? $location->content : [],
                metaTitle: $location->meta_title,
                metaDescription: $location->meta_description,
            );
        } catch (Throwable) {
            return null;
        }
    }

    private static function codebaseTownship(string $countySlug, string $townshipSlug): ?object
    {
        $county = self::codebaseCounties()[$countySlug] ?? null;

        if (! $county) {
            return null;
        }

        $township = collect($county['townships'])
            ->first(fn (array $township): bool => $township['slug'] === $townshipSlug);

        if (! $township) {
            return null;
        }

        return self::makeTownshipPage(
            countyName: $county['name'],
            townshipName: $township['name'],
            countySlug: $countySlug,
            townshipSlug: $townshipSlug,
            townships: $county['townships'],
        );
    }

    private static function makeTownshipPage(
        string $countyName,
        string $townshipName,
        string $countySlug,
        string $townshipSlug,
        array $content = [],
        ?string $metaTitle = null,
        ?string $metaDescription = null,
        ?array $townships = null,
    ): object {
        $meta = self::defaultTownshipMeta($countyName, $townshipName, $countySlug, $townshipSlug);

        return (object) [
            'county_name' => $countyName,
            'county_slug' => $countySlug,
            'township_name' => $townshipName,
            'township_slug' => $townshipSlug,
            'meta_title' => $metaTitle ?: $meta['title'],
            'meta_description' => $metaDescription ?: $meta['description'],
            'content' => array_replace_recursive(
                self::defaultTownshipContent($countyName, $townshipName, $countySlug, $townshipSlug),
                self::stripEmptyValues($content),
            ),
            'townships' => self::normalizeTownshipList($townships ?? self::townshipsForCounty($countySlug)),
        ];
    }

    private static function defaultTownshipMeta(
        string $countyName,
        string $townshipName,
        string $countySlug,
        string $townshipSlug,
    ): array {
        if ($countySlug === 'cook-county' && $townshipSlug === 'barrington-township') {
            return [
                'title' => 'Best In-Home Care & Senior Services in Barrington Township, Cook County | WestHub Healthcare',
                'description' => 'Looking for professional in-home skilled nursing or caregiving in Barrington Township? WestHub Healthcare provides top-rated home care, homemaking, and aide services.',
            ];
        }

        return [
            'title' => "In-Home Care & Senior Services in {$townshipName}, {$countyName} | WestHub Healthcare",
            'description' => "Looking for professional in-home skilled nursing or caregiving in {$townshipName}? WestHub Healthcare provides top-rated home care services.",
        ];
    }

    private static function defaultTownshipContent(
        string $countyName,
        string $townshipName,
        string $countySlug,
        string $townshipSlug,
    ): array {
        if ($countySlug === 'cook-county' && $townshipSlug === 'barrington-township') {
            return [
                'hero' => [
                    'title' => 'WestHub Healthcare Services in Barrington Township',
                    'cta_text' => 'Book Appointment',
                ],
                'details' => [
                    'title' => 'Barrington Township In-Home Skilled Nurses, Caregivers & Home Healthcare Aides',
                    'subtitle' => 'Meal Preparation, Light Housekeeping, Bathing & Dressing, Medication Reminders, Incontinence, Alzheimer\'s/Dementia, Respite care, and Errands.',
                    'mission' => 'Our Mission Statement is to be a top-rated home healthcare service provider in Barrington Township by using excellent human skills and technology to differentiate our services, thereby continually seeking development of our staff and processes based on current evidence-based industry techniques for the most creative, innovative, and personalized homecare experiences that promote the overall health, wellbeing, and independence our clients and patients',
                    'secondary_title' => 'Caregivers Rendering Exceptional Home and senior care services in Barrington Township',
                    'secondary_description' => 'At WestHub Healthcare, we believe that the best place to heal and thrive is at home. As Barrington Township\'s trusted choice for professional home health care, our mission is to support your wellbeing through high-quality services tailored to your life.',
                    'promise' => 'We prioritize your comfort above all else. Our team of compassionate experts works closely with you to deliver care that isn\'t just professional; it\'s personal. Experience the peace of mind that comes with a team dedicated to your unique journey.',
                ],
                'offerings' => [
                    'title' => 'Our Offerings: Senior Home Care in Barrington Township',
                    'items' => [
                        [
                            'title' => 'Your Trusted Choice for Care in Barrington Township',
                            'description' => 'At WestHub Healthcare, we believe that professional support should feel personal. Our diverse care options range from helping with daily routines to high-level clinical oversight, all delivered within the comfort of your own home.',
                        ],
                        [
                            'title' => 'Helpful Hourly Homemaking',
                            'description' => 'At WestHub Healthcare, our believe is deeply rooted in a tidy, peaceful home is the heart of well-being. Our dedicated homemakers in Barrington Township and Chicago provide a helping hand with the daily essentials; from light housekeeping and laundry to preparing your favorite nutritious meals.',
                        ],
                        [
                            'title' => 'Home Health Aide Services',
                            'description' => 'At WestHub Healthcare, we believe that every individual deserves to feel safe, dignified, and truly cared for in their own home. Our compassionate Home Health Aides are here to provide a helping hand with the personal tasks of daily life in Barrington Township.',
                        ],
                        [
                            'title' => 'A Gentle Transition Home',
                            'description' => 'Returning home after a hospital stay should be a time of peace and recovery. At WestHub Healthcare, our Barrington Township team is here to walk beside you, providing the heartfelt support needed for a safe and comfortable transition back to your own surroundings.',
                        ],
                    ],
                ],
                'footer_cta' => [
                    'title' => "Get in touch for the Best Senior Care Services in {$townshipName}.",
                    'description' => "Explore how our Home Health Care Services in {$townshipName} can enhance your loved one's quality of life. Contact our {$townshipName} Caregivers today for personalized care solutions tailored to your needs.",
                ],
            ];
        }

        return [
            'hero' => [
                'title' => "WestHub Healthcare Services in {$townshipName}",
                'cta_text' => 'Book Appointment',
            ],
            'details' => [
                'title' => "{$townshipName} In-Home Skilled Nurses, Caregivers & Home Healthcare Aides",
                'subtitle' => 'Meal Preparation, Light Housekeeping, Bathing & Dressing, Medication Reminders, Incontinence, Alzheimer\'s/Dementia, Respite care, and Errands.',
                'mission' => "Our Mission Statement is to be a top-rated home healthcare service provider in {$townshipName} by using excellent human skills and technology to differentiate our services, thereby continually seeking development of our staff and processes.",
                'secondary_title' => "Caregivers Rendering Exceptional Home care in {$townshipName}",
                'secondary_description' => "At WestHub Healthcare, we believe that the best place to heal and thrive is at home. As {$townshipName}'s trusted choice for professional home health care, our mission is to support your wellbeing.",
                'promise' => "We prioritize your comfort above all else. Our team of compassionate experts works closely with you to deliver care that isn't just professional; it's personal.",
            ],
            'offerings' => [
                'title' => "Our Offerings: Senior Home Care in {$townshipName}",
                'items' => [
                    [
                        'title' => "Your Trusted Choice for Care in {$townshipName}",
                        'description' => "At WestHub Healthcare, we believe that professional support should feel personal. Our diverse care options range from helping with daily routines to high-level clinical oversight.",
                    ],
                    [
                        'title' => "Helpful Hourly Homemaking in {$townshipName}",
                        'description' => "Our dedicated homemakers in {$townshipName} provide a helping hand with the daily essentials; from light housekeeping and laundry to preparing nutritious meals.",
                    ],
                    [
                        'title' => 'Home Health Aide Services',
                        'description' => "Our compassionate Home Health Aides are here to provide a helping hand with the personal tasks of daily life in {$townshipName}.",
                    ],
                    [
                        'title' => 'A Gentle Transition Home',
                        'description' => "Returning home after a hospital stay should be a time of peace and recovery. Our {$townshipName} team is here to walk beside you.",
                    ],
                ],
            ],
            'footer_cta' => [
                'title' => "Get in touch for the Best Senior Care Services in {$townshipName}.",
                'description' => "Explore how our Home Health Care Services in {$townshipName} can enhance your loved one's quality of life. Contact our {$townshipName} Caregivers today.",
            ],
        ];
    }

    private static function activeScope($query, array $columns): void
    {
        if (in_array('is_active', $columns, true)) {
            $query->where('is_active', true);
        }
    }

    private static function locationOrder($query, array $columns): void
    {
        if (in_array('sort_order', $columns, true)) {
            $query->orderBy('sort_order');
        }

        if (in_array('name', $columns, true)) {
            $query->orderBy('name');
        }
    }

    private static function contentSchema()
    {
        return Schema::connection((new County())->getConnectionName());
    }

    private static function stripEmptyValues(array $content): array
    {
        foreach ($content as $key => $value) {
            if (is_array($value)) {
                $content[$key] = self::stripEmptyValues($value);
                continue;
            }

            if ($value === null || (is_string($value) && trim($value) === '')) {
                unset($content[$key]);
            }
        }

        return $content;
    }

    private static function codebaseCounties(): array
    {
        $counties = self::fallbackCounties();

        foreach ($counties as &$county) {
            $county['townships'] = self::normalizeTownshipList($county['townships'] ?? []);
        }

        return $counties;
    }

    private static function codebaseServiceLocationLinks(): array
    {
        $locations = collect(self::fallbackServiceLocations())
            ->map(function (array $location, int $index): array {
                $countySlug = $location['county_slug'];
                $type = $location['type'] ?? 'township';

                return [
                    'name' => $location['name'],
                    'href' => $type === 'county'
                        ? route('locations.county', ['county' => $countySlug])
                        : route('locations.township', [
                            'county' => $countySlug,
                            'township' => $location['township_slug'] ?? str($location['name'])->slug()->toString(),
                        ]),
                    'sort_order' => $index + 1,
                ];
            })
            ->all();

        return self::dedupeServiceLocationLinks($locations);
    }

    private static function dedupeServiceLocationLinks(array $locations): array
    {
        $deduped = collect($locations)
            ->filter(fn (array $location): bool => trim((string) ($location['name'] ?? '')) !== '')
            ->sortBy([
                ['sort_order', 'asc'],
                ['name', 'asc'],
            ])
            ->reduce(function (array $carry, array $location): array {
                $key = self::serviceLocationKey($location['name']);

                if (! isset($carry[$key])) {
                    $carry[$key] = [
                        'name' => $location['name'],
                        'href' => $location['href'],
                    ];
                }

                return $carry;
            }, []);

        return array_values($deduped);
    }

    private static function serviceLocationKey(string $name): string
    {
        return str($name)
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', ' ')
            ->squish()
            ->toString();
    }

    private static function defaultCoords(): array
    {
        return [41.8781, -87.6298];
    }

    /**
     * Codebase fallback data for supported counties.
     */
    private static function fallbackCounties(bool $includeSortOrder = false): array
    {
        $counties = self::fallbackCountyDefinitions();

        foreach ($counties as &$county) {
            $county['townships'] = [];
        }

        foreach (self::fallbackServiceLocations() as $index => $location) {
            $countySlug = $location['county_slug'];
            $sortOrder = $index + 1;

            if (! isset($counties[$countySlug])) {
                continue;
            }

            if (($location['type'] ?? 'township') === 'county') {
                $counties[$countySlug]['name'] = $location['name'];
                $counties[$countySlug]['sort_order'] = $sortOrder;

                continue;
            }

            $township = [
                'name' => $location['name'],
                'slug' => $location['township_slug'] ?? str($location['name'])->slug()->toString(),
            ];

            if ($includeSortOrder) {
                $township['sort_order'] = $sortOrder;
            }

            $counties[$countySlug]['townships'][] = $township;
        }

        return $counties;
    }

    private static function fallbackCountyDefinitions(): array
    {
        return [
            'cook-county' => [
                'name' => 'Cook County',
                'coords' => [41.8414, -87.8165],
                'subtitle' => 'Skilled Nurses, Caregivers & Home Healthcare Aides',
            ],
            'lake-county' => [
                'name' => 'Lake County',
                'coords' => [42.3480, -88.0559],
                'subtitle' => 'Professional Nursing & Dedicated Caregivers',
            ],
            'dupage-county' => [
                'name' => 'Du Page County',
                'coords' => [41.8310, -88.0817],
                'subtitle' => 'Compassionate Senior Care & Therapeutic Services',
            ],
            'will-county' => [
                'name' => 'Will County',
                'coords' => [41.3853, -87.9786],
                'subtitle' => 'Trusted Home Healthcare & Medical Support',
            ],
            'kankakee-county' => [
                'name' => 'Kankakee County',
                'coords' => [41.1384, -87.8617],
                'subtitle' => 'Factual Home Nursing & Health Aides',
            ],
            'kendall-county' => [
                'name' => 'Kendall County',
                'coords' => [41.5878, -88.4281],
                'subtitle' => 'Expanding Care Services in Kendall Region',
            ],
            'grundy-county' => [
                'name' => 'Grundy County & surrounding areas',
                'coords' => [41.2858, -88.4208],
                'subtitle' => 'Personalized Care for Grundy Residents',
            ],
        ];
    }

    private static function fallbackServiceLocations(): array
    {
        return [
            ['name' => 'Addison', 'county_slug' => 'dupage-county'],
            ['name' => 'Antioch', 'county_slug' => 'lake-county'],
            ['name' => 'Aurora', 'county_slug' => 'dupage-county'],
            ['name' => 'Barrington', 'county_slug' => 'cook-county'],
            ['name' => 'Barrington Township', 'county_slug' => 'cook-county'],
            ['name' => 'Bartlett', 'county_slug' => 'dupage-county'],
            ['name' => 'Bensenville', 'county_slug' => 'dupage-county'],
            ['name' => 'Berwyn Township', 'county_slug' => 'cook-county'],
            ['name' => 'Bloom Township', 'county_slug' => 'cook-county'],
            ['name' => 'Bloomingdale', 'county_slug' => 'dupage-county'],
            ['name' => 'Bremen Township', 'county_slug' => 'cook-county'],
            ['name' => 'Buffalo Grove', 'county_slug' => 'lake-county'],
            ['name' => 'Calumet Township', 'county_slug' => 'cook-county'],
            ['name' => 'Carol Stream', 'county_slug' => 'dupage-county'],
            ['name' => 'Chicago IL', 'county_slug' => 'cook-county'],
            ['name' => 'Cicero Township', 'county_slug' => 'cook-county'],
            ['name' => 'Clarendon Hills', 'county_slug' => 'dupage-county'],
            ['name' => 'Cook County', 'county_slug' => 'cook-county', 'type' => 'county'],
            ['name' => 'Darien', 'county_slug' => 'dupage-county'],
            ['name' => 'Deerfield', 'county_slug' => 'lake-county'],
            ['name' => 'Downers Grove', 'county_slug' => 'dupage-county'],
            ['name' => 'Du Page County', 'county_slug' => 'dupage-county', 'type' => 'county'],
            ['name' => 'Elk Grove Township', 'county_slug' => 'cook-county'],
            ['name' => 'Elmhurst', 'county_slug' => 'dupage-county'],
            ['name' => 'Eola', 'county_slug' => 'dupage-county'],
            ['name' => 'Evanston Township', 'county_slug' => 'cook-county'],
            ['name' => 'Fort Sheridan', 'county_slug' => 'lake-county'],
            ['name' => 'Fox Lake', 'county_slug' => 'lake-county'],
            ['name' => 'Fox Valley', 'county_slug' => 'dupage-county'],
            ['name' => 'Glen Ellyn', 'county_slug' => 'dupage-county'],
            ['name' => 'Glendale Heights', 'county_slug' => 'dupage-county'],
            ['name' => 'Grayslake', 'county_slug' => 'lake-county'],
            ['name' => 'Great Lakes', 'county_slug' => 'lake-county'],
            ['name' => 'Grundy County & surrounding areas', 'county_slug' => 'grundy-county', 'type' => 'county'],
            ['name' => 'Gurnee', 'county_slug' => 'lake-county'],
            ['name' => 'Hanover Township', 'county_slug' => 'cook-county'],
            ['name' => 'Highland Park', 'county_slug' => 'lake-county'],
            ['name' => 'Highwood', 'county_slug' => 'lake-county'],
            ['name' => 'Hinsdale', 'county_slug' => 'dupage-county'],
            ['name' => 'Ingleside', 'county_slug' => 'lake-county'],
            ['name' => 'Island Lake', 'county_slug' => 'lake-county'],
            ['name' => 'Itasca', 'county_slug' => 'dupage-county'],
            ['name' => 'Kankakee County', 'county_slug' => 'kankakee-county', 'type' => 'county'],
            ['name' => 'Kendall County', 'county_slug' => 'kendall-county', 'type' => 'county'],
            ['name' => 'Lake Bluff', 'county_slug' => 'lake-county'],
            ['name' => 'Lake County', 'county_slug' => 'lake-county', 'type' => 'county'],
            ['name' => 'Lake Forest', 'county_slug' => 'lake-county'],
            ['name' => 'Lake Villa', 'county_slug' => 'lake-county'],
            ['name' => 'Lake Zurich', 'county_slug' => 'lake-county'],
            ['name' => 'Lemont Township', 'county_slug' => 'cook-county'],
            ['name' => 'Leyden Township', 'county_slug' => 'cook-county'],
            ['name' => 'Libertyville', 'county_slug' => 'lake-county'],
            ['name' => 'Lincolnshire', 'county_slug' => 'lake-county'],
            ['name' => 'Lisle', 'county_slug' => 'dupage-county'],
            ['name' => 'Lombard', 'county_slug' => 'dupage-county'],
            ['name' => 'Lyons Township', 'county_slug' => 'cook-county'],
            ['name' => 'Maine Township', 'county_slug' => 'cook-county'],
            ['name' => 'Medinah', 'county_slug' => 'dupage-county'],
            ['name' => 'Mundelein', 'county_slug' => 'lake-county'],
            ['name' => 'Naperville', 'county_slug' => 'dupage-county'],
            ['name' => 'New Trier Township', 'county_slug' => 'cook-county'],
            ['name' => 'Niles Township', 'county_slug' => 'cook-county'],
            ['name' => 'North Chicago', 'county_slug' => 'lake-county'],
            ['name' => 'Northfield Township', 'county_slug' => 'cook-county'],
            ['name' => 'Norwood Park Township', 'county_slug' => 'cook-county'],
            ['name' => 'Oak Brook', 'county_slug' => 'dupage-county'],
            ['name' => 'Oak Park Township', 'county_slug' => 'cook-county'],
            ['name' => 'Orland Township', 'county_slug' => 'cook-county'],
            ['name' => 'Palatine Township', 'county_slug' => 'cook-county'],
            ['name' => 'Palos Township', 'county_slug' => 'cook-county'],
            ['name' => 'Proviso Township', 'county_slug' => 'cook-county'],
            ['name' => 'Rich Township', 'county_slug' => 'cook-county'],
            ['name' => 'River Forest', 'county_slug' => 'cook-county'],
            ['name' => 'Riverside Township', 'county_slug' => 'cook-county'],
            ['name' => 'Roselle', 'county_slug' => 'dupage-county'],
            ['name' => 'Round Lake', 'county_slug' => 'lake-county'],
            ['name' => 'Russell', 'county_slug' => 'lake-county'],
            ['name' => 'Schaumburg Township', 'county_slug' => 'cook-county'],
            ['name' => 'Stickney Township', 'county_slug' => 'cook-county'],
            ['name' => 'Thornton Township', 'county_slug' => 'cook-county'],
            ['name' => 'Vernon Hills', 'county_slug' => 'lake-county'],
            ['name' => 'Villa Park', 'county_slug' => 'dupage-county'],
            ['name' => 'Wadsworth', 'county_slug' => 'lake-county'],
            ['name' => 'Warrenville', 'county_slug' => 'dupage-county'],
            ['name' => 'Wauconda', 'county_slug' => 'lake-county'],
            ['name' => 'Waukegan', 'county_slug' => 'lake-county'],
            ['name' => 'Wayne', 'county_slug' => 'dupage-county'],
            ['name' => 'West Chicago', 'county_slug' => 'dupage-county'],
            ['name' => 'Westmont', 'county_slug' => 'dupage-county'],
            ['name' => 'Wheaton', 'county_slug' => 'dupage-county'],
            ['name' => 'Wheeling Township', 'county_slug' => 'cook-county'],
            ['name' => 'Will County', 'county_slug' => 'will-county', 'type' => 'county'],
            ['name' => 'Willowbrook', 'county_slug' => 'dupage-county'],
            ['name' => 'Winfield', 'county_slug' => 'dupage-county'],
            ['name' => 'Winthrop Harbor', 'county_slug' => 'lake-county'],
            ['name' => 'Wood Dale', 'county_slug' => 'dupage-county'],
            ['name' => 'Woodridge', 'county_slug' => 'dupage-county'],
            ['name' => 'Worth Township', 'county_slug' => 'cook-county'],
            ['name' => 'Zion', 'county_slug' => 'lake-county'],
        ];
    }

    /**
     * Check whether a table exists on the given schema builder, caching the
     * result statically so we never fire the information_schema query more
     * than once per request per table.
     */
    private static function schemaHasTable(\Illuminate\Database\Schema\Builder $schema, string $table): bool
    {
        $connection = $schema->getConnection()->getName();
        $key        = "{$connection}.{$table}";

        if (! array_key_exists($key, self::$schemaTableCache)) {
            self::$schemaTableCache[$key] = $schema->hasTable($table);
        }

        return self::$schemaTableCache[$key];
    }
}
