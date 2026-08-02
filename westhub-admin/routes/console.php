<?php

use App\Jobs\Seo\IngestSeoMetricsJob;
use Illuminate\Support\Facades\Schedule;

Schedule::job(new IngestSeoMetricsJob('ga4'))->hourly();
Schedule::job(new IngestSeoMetricsJob('search_console'))->dailyAt('02:00');
