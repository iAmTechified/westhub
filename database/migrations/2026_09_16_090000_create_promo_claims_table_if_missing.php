<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mirror of the authoritative migration in westhub-admin. The table is shared,
 * so this only creates it when the admin app has not already done so, and its
 * down() is intentionally a no-op to avoid dropping a table the admin owns.
 */
return new class extends Migration
{
    public function getConnection(): ?string
    {
        return config('database.content_connection', 'content');
    }

    public function up(): void
    {
        if (Schema::connection($this->getConnection())->hasTable('promo_claims')) {
            return;
        }

        Schema::connection($this->getConnection())->create('promo_claims', function (Blueprint $table) {
            // Must match the admin migration's engine: MyISAM caps a key at
            // 1000 bytes and the email and (campaign, email) indexes exceed it
            // under utf8mb4.
            $table->engine('InnoDB');

            $table->id();
            $table->string('campaign', 64)->default('free_month')->index();
            $table->string('full_name');
            $table->string('email')->index();
            $table->string('phone')->nullable();
            $table->unsignedBigInteger('service_id')->nullable();
            $table->string('voucher_code', 40)->unique();
            $table->string('status', 32)->default('new')->index();
            $table->timestamp('consent_at')->nullable();
            $table->string('source_page')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('emailed_at')->nullable();
            $table->timestamp('contacted_at')->nullable();
            $table->timestamp('redeemed_at')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->unsignedBigInteger('appointment_id')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['campaign', 'email']);
        });
    }

    public function down(): void
    {
        // The admin app owns this table's lifecycle.
    }
};
