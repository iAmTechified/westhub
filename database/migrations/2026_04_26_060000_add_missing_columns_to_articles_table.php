<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            if (! Schema::hasColumn('articles', 'article_category_id')) {
                $table->foreignId('article_category_id')->nullable()->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('articles', 'slug')) {
                $table->string('slug')->unique();
            }
            if (! Schema::hasColumn('articles', 'excerpt')) {
                $table->text('excerpt')->nullable();
            }
            if (! Schema::hasColumn('articles', 'body')) {
                $table->longText('body')->nullable();
            }
            if (! Schema::hasColumn('articles', 'headline_image_path')) {
                $table->string('headline_image_path')->nullable();
            }
            if (! Schema::hasColumn('articles', 'status')) {
                $table->string('status', 32)->default('draft')->index();
            }
            if (! Schema::hasColumn('articles', 'seo_title')) {
                $table->string('seo_title')->nullable();
            }
            if (! Schema::hasColumn('articles', 'seo_description')) {
                $table->text('seo_description')->nullable();
            }
            if (! Schema::hasColumn('articles', 'canonical_url')) {
                $table->string('canonical_url')->nullable();
            }
            if (! Schema::hasColumn('articles', 'focus_keyword')) {
                $table->string('focus_keyword')->nullable()->index();
            }
            if (! Schema::hasColumn('articles', 'og_image')) {
                $table->string('og_image')->nullable();
            }
            if (! Schema::hasColumn('articles', 'read_time')) {
                $table->unsignedSmallInteger('read_time')->nullable();
            }
            if (! Schema::hasColumn('articles', 'last_saved_at')) {
                $table->timestamp('last_saved_at')->nullable();
            }
            if (! Schema::hasColumn('articles', 'last_edited_at')) {
                $table->timestamp('last_edited_at')->nullable();
            }
            if (! Schema::hasColumn('articles', 'published_at')) {
                $table->timestamp('published_at')->nullable()->index();
            }
            if (! Schema::hasColumn('articles', 'scheduled_for')) {
                $table->timestamp('scheduled_for')->nullable()->index();
            }
            if (! Schema::hasColumn('articles', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn([
                'article_category_id', 'slug', 'excerpt', 'body',
                'headline_image_path', 'status', 'seo_title', 'seo_description',
                'canonical_url', 'focus_keyword', 'og_image', 'read_time',
                'last_saved_at', 'last_edited_at', 'published_at',
                'scheduled_for', 'deleted_at',
            ]);
        });
    }
};
