<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $connection = config('database.content_connection', 'content');

        if (Schema::connection($connection)->hasTable('appointment_events')) {
            return;
        }

        Schema::connection($connection)->create('appointment_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event_type');
            $table->string('old_status')->nullable();
            $table->string('new_status')->nullable();
            $table->text('note')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('event_at')->useCurrent()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // The public site shares the admin content database, so rollback should
        // not drop a table that may be owned by the admin application.
    }
};
