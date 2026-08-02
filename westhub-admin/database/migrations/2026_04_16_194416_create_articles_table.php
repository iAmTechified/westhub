<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
                if (Schema::hasTable('articles')) {
            return;
        }

        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('body')->nullable();
            $table->string('headline_image_path')->nullable();
            $table->string('status', 32)->default('draft')->index();

            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->string('canonical_url')->nullable();
            $table->string('focus_keyword')->nullable()->index();
            $table->string('og_image')->nullable();
            $table->unsignedSmallInteger('read_time')->nullable();

            $table->timestamp('last_saved_at')->nullable();
            $table->timestamp('last_edited_at')->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamp('scheduled_for')->nullable()->index();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['status', 'article_category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
