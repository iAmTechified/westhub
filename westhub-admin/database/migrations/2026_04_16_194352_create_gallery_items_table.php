<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
                if (Schema::hasTable('gallery_items')) {
            return;
        }

        Schema::create('gallery_items', function (Blueprint $table) {
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

    public function down(): void
    {
        Schema::dropIfExists('gallery_items');
    }
};
