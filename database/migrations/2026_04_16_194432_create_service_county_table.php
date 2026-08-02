<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
                if (Schema::hasTable('service_county')) {
            return;
        }

        Schema::create('service_county', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('county_id')->constrained()->cascadeOnDelete();
            $table->string('availability_status')->default('available');
            $table->timestamps();

            $table->unique(['service_id', 'county_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_county');
    }
};
