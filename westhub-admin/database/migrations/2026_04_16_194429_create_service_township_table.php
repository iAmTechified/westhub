<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
                if (Schema::hasTable('service_township')) {
            return;
        }

        Schema::create('service_township', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('township_id')->constrained()->cascadeOnDelete();
            $table->string('availability_status')->default('available');
            $table->timestamps();

            $table->unique(['service_id', 'township_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_township');
    }
};
