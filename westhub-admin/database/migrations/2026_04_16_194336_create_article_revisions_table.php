<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
                if (Schema::hasTable('article_revisions')) {
            return;
        }

        Schema::create('article_revisions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('article_id')->index();
            $table->foreignId('saved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('version');
            $table->json('payload_json');
            $table->timestamp('saved_at')->useCurrent()->index();
            $table->timestamps();

            $table->unique(['article_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_revisions');
    }
};
