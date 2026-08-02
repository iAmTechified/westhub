<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
                if (Schema::hasTable('outbound_messages')) {
            return;
        }

        Schema::create('outbound_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('join_request_id')->nullable()->constrained()->nullOnDelete();
            $table->string('recipient_email')->index();
            $table->string('template_key');
            $table->string('provider')->nullable();
            $table->string('status', 32)->default('queued')->index();
            $table->string('subject')->nullable();
            $table->longText('body')->nullable();
            $table->json('meta')->nullable();
            $table->json('provider_response')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outbound_messages');
    }
};
