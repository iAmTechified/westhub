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
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('event_type_name')->nullable()->after('service_id');
            $table->timestamp('end_time')->nullable()->after('scheduled_at');
            $table->string('calendly_event_id')->nullable()->index()->after('meta');
            $table->string('calendly_invitee_id')->nullable()->index()->after('calendly_event_id');
            $table->string('cancel_url')->nullable()->after('calendly_invitee_id');
            $table->string('reschedule_url')->nullable()->after('cancel_url');
        });

        Schema::table('outbound_messages', function (Blueprint $table) {
            $table->foreignId('appointment_id')->nullable()->after('join_request_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('outbound_messages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('appointment_id');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn([
                'event_type_name',
                'end_time',
                'calendly_event_id',
                'calendly_invitee_id',
                'cancel_url',
                'reschedule_url',
            ]);
        });
    }
};
