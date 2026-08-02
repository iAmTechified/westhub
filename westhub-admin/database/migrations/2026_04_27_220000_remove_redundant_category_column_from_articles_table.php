<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            if (Schema::hasColumn('articles', 'category')) {
                $table->dropColumn('category');
            }

            // Ensure these columns are nullable as expected by the application
            $table->text('excerpt')->nullable()->change();
            $table->longText('body')->nullable()->change();
            $table->string('headline_image_path')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->string('category')->nullable();
        });
    }
};
