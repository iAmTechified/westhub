<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $connection = config('database.content_connection', 'content');

        if (Schema::connection($connection)->hasTable('appointments')) {
            return;
        }

        Schema::connection($connection)->create('appointments', function (Blueprint $table): void {
            $table->id();
            $table->string('full_name');
            $table->string('email')->index();
            $table->string('phone')->nullable();
            $table->foreignId('county_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('township_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->date('preferred_date')->nullable()->index();
            $table->string('preferred_time')->nullable();
            $table->text('message')->nullable();
            $table->string('source', 32)->default('website')->index();
            $table->string('status', 32)->default('new')->index();
            $table->timestamp('scheduled_at')->nullable()->index();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        // The public site shares the admin content database, so rollback should
        // not drop a table that may be owned by the admin application.
    }
};
