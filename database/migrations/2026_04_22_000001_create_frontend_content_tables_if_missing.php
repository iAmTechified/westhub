<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $connection = config('database.content_connection', 'content');
        $schema = Schema::connection($connection);

        if (! $schema->hasTable('media')) {
            $schema->create('media', function (Blueprint $table) {
                $table->id();
                $table->morphs('model');
                $table->uuid()->nullable()->unique();
                $table->string('collection_name');
                $table->string('name');
                $table->string('file_name');
                $table->string('mime_type')->nullable();
                $table->string('disk');
                $table->string('conversions_disk')->nullable();
                $table->unsignedBigInteger('size');
                $table->json('manipulations');
                $table->json('custom_properties');
                $table->json('generated_conversions');
                $table->json('responsive_images');
                $table->unsignedInteger('order_column')->nullable()->index();
                $table->nullableTimestamps();
            });
        }

        if (! $schema->hasTable('settings')) {
            $schema->create('settings', function (Blueprint $table) use ($schema) {
                $table->id();
                $table->string('group', 64)->index();
                $table->string('key', 100);
                $table->longText('value')->nullable();
                $table->string('type')->default('string');
                $table->boolean('is_encrypted')->default(false);
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();

                $table->unique(['group', 'key']);

                if ($schema->hasTable('users')) {
                    $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
                }
            });
        }

        if (! $schema->hasTable('subscribers')) {
            $schema->create('subscribers', function (Blueprint $table) {
                $table->id();
                $table->string('email')->unique();
                $table->string('full_name')->nullable();
                $table->string('source', 32)->default('website')->index();
                $table->string('status', 32)->default('subscribed')->index();
                $table->timestamp('subscribed_at')->nullable()->index();
                $table->timestamp('unsubscribed_at')->nullable()->index();
                $table->json('meta')->nullable();
                $table->timestamps();

                $table->index(['status', 'source']);
            });
        }

        if (! $schema->hasTable('testimonials')) {
            $schema->create('testimonials', function (Blueprint $table) {
                $table->id();
                $table->string('author_name');
                $table->string('author_role')->nullable();
                $table->text('quote');
                $table->unsignedTinyInteger('rating')->default(5);
                $table->string('avatar_path')->nullable();
                $table->string('status', 32)->default('draft')->index();
                $table->boolean('is_featured')->default(false)->index();
                $table->unsignedInteger('sort_order')->default(0)->index();
                $table->timestamp('published_at')->nullable()->index();
                $table->timestamps();

                $table->index(['status', 'is_featured']);
            });
        }

        if (! $schema->hasTable('gallery_categories')) {
            $schema->create('gallery_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();
            });
        }

        if (! $schema->hasTable('gallery_items')) {
            $schema->create('gallery_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('gallery_category_id')->nullable()->constrained()->nullOnDelete();
                $table->string('title');
                $table->string('slug')->unique();
                $table->string('alt_text')->nullable();
                $table->text('caption')->nullable();
                $table->string('visibility', 32)->default('private')->index();
                $table->string('status', 32)->default('draft')->index();
                $table->unsignedInteger('sort_order')->default(0)->index();
                $table->timestamp('published_at')->nullable();
                $table->timestamps();

                $table->index(['status', 'visibility']);
            });
        }

        if (! $schema->hasTable('appointments')) {
            $schema->create('appointments', function (Blueprint $table) use ($schema) {
                $table->id();
                $table->string('full_name');
                $table->string('email')->index();
                $table->string('phone')->nullable();
                $table->unsignedBigInteger('county_id')->nullable();
                $table->unsignedBigInteger('township_id')->nullable();
                $table->unsignedBigInteger('service_id')->nullable();
                $table->date('preferred_date')->nullable()->index();
                $table->string('preferred_time')->nullable();
                $table->text('message')->nullable();
                $table->string('source', 32)->default('website')->index();
                $table->string('status', 32)->default('new')->index();
                $table->timestamp('scheduled_at')->nullable()->index();
                $table->unsignedBigInteger('assigned_to')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->json('meta')->nullable();
                $table->timestamps();

                $table->index(['status', 'created_at']);

                if ($schema->hasTable('counties')) {
                    $table->foreign('county_id')->references('id')->on('counties')->nullOnDelete();
                }

                if ($schema->hasTable('townships')) {
                    $table->foreign('township_id')->references('id')->on('townships')->nullOnDelete();
                }

                if ($schema->hasTable('services')) {
                    $table->foreign('service_id')->references('id')->on('services')->nullOnDelete();
                }

                if ($schema->hasTable('users')) {
                    $table->foreign('assigned_to')->references('id')->on('users')->nullOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        // The website shares content tables with the admin application, so this
        // rollback intentionally avoids dropping shared backend data.
    }
};
