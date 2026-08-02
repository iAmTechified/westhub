<?php

namespace App\Jobs\Seo;

use App\Models\Article;
use App\Models\SeoMetric;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class IngestSeoMetricsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public function __construct(public ?string $source = null)
    {
    }

    public function handle(): void
    {
        $source = $this->source ?: 'ga4';

        // TODO: replace mock ingestion with real GA4 + Search Console API calls once credentials are set.
        Article::query()->where('status', Article::STATUS_PUBLISHED)->take(50)->get()->each(function (Article $article) use ($source) {
            SeoMetric::updateOrCreate(
                [
                    'entity_type' => Article::class,
                    'entity_id' => $article->id,
                    'metric_date' => now()->toDateString(),
                    'source' => $source,
                ],
                [
                    'clicks' => random_int(20, 200),
                    'impressions' => random_int(200, 2000),
                    'ctr' => random_int(1, 30),
                    'position' => random_int(3, 45),
                    'page_url' => '/articles/' . $article->slug,
                    'top_queries' => ['home care', 'healthcare illinois'],
                ]
            );
        });
    }
}
