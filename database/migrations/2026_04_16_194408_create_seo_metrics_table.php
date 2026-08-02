<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
                if (Schema::hasTable('seo_metrics')) {
            return;
        }

        Schema::create('seo_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type', 64)->index();
            $table->unsignedBigInteger('entity_id')->index();
            $table->date('metric_date')->index();
            $table->unsignedInteger('clicks')->default(0);
            $table->unsignedInteger('impressions')->default(0);
            $table->decimal('ctr', 5, 2)->default(0);
            $table->decimal('position', 8, 2)->nullable();
            $table->string('page_url')->nullable();
            $table->json('top_queries')->nullable();
            $table->string('source', 32)->default('ga4');
            $table->timestamps();

            $table->unique(['entity_type', 'entity_id', 'metric_date', 'source'], 'seo_metrics_unique_entity_day_source');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_metrics');
    }
};
