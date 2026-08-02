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

        if (! $schema->hasTable('counties')) {
            $schema->create('counties', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->string('meta_title')->nullable();
                $table->text('meta_description')->nullable();
                $table->boolean('is_active')->default(true)->index();
                $table->unsignedInteger('sort_order')->default(0)->index();
                $table->timestamps();
            });
        }

        if (! $schema->hasTable('townships')) {
            $schema->create('townships', function (Blueprint $table) use ($schema) {
                $table->id();
                $table->unsignedBigInteger('county_id');
                $table->string('name');
                $table->string('slug');
                $table->json('content')->nullable();
                $table->string('meta_title')->nullable();
                $table->text('meta_description')->nullable();
                $table->boolean('is_active')->default(true)->index();
                $table->unsignedInteger('sort_order')->default(0)->index();
                $table->timestamps();

                $table->unique(['county_id', 'slug']);

                if ($schema->hasTable('counties')) {
                    $table->foreign('county_id')->references('id')->on('counties')->cascadeOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        // Shared content tables are preserved on rollback.
    }
};
