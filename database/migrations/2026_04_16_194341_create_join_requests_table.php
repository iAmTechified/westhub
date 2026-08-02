<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
                if (Schema::hasTable('join_requests')) {
            return;
        }

        Schema::create('join_requests', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('email')->index();
            $table->string('phone')->nullable();
            $table->string('profession')->nullable()->index();
            $table->text('about')->nullable();
            $table->json('qualifications')->nullable();
            $table->string('resume_path')->nullable();
            $table->text('internal_notes')->nullable();
            $table->string('status', 32)->default('new')->index();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('join_requests');
    }
};
