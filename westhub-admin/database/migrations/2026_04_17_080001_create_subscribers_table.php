<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
                if (Schema::hasTable('subscribers')) {
            return;
        }

        Schema::create('subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('full_name')->nullable();
            $table->string('source', 32)->default('website')->index();
            $table->string('status', 32)->default('subscribed')->index();
            $table->timestamp('subscribed_at')->nullable()->index();
            $table->timestamp('unsubscribed_at')->nullable()->index();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['status', 'source']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscribers');
    }
};
