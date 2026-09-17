<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('promo_claims')) {
            return;
        }

        Schema::create('promo_claims', function (Blueprint $table) {
            $table->id();
            $table->string('campaign', 64)->default('free_month')->index();
            $table->string('full_name');
            $table->string('email')->index();
            $table->string('phone')->nullable();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->string('voucher_code', 40)->unique();
            $table->string('status', 32)->default('new')->index();
            $table->timestamp('consent_at')->nullable();
            $table->string('source_page')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('emailed_at')->nullable();
            $table->timestamp('contacted_at')->nullable();
            $table->timestamp('redeemed_at')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['campaign', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_claims');
    }
};
