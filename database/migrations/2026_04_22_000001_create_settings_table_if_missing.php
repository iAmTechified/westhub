<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $connection = config('database.content_connection', 'content');

        if (Schema::connection($connection)->hasTable('settings')) {
            return;
        }

        Schema::connection($connection)->create('settings', function (Blueprint $table): void {
            $table->id();
            $table->string('group', 64)->index();
            $table->string('key', 100);
            $table->longText('value')->nullable();
            $table->string('type')->default('string');
            $table->boolean('is_encrypted')->default(false);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['group', 'key']);
        });
    }

    public function down(): void
    {
        // The public site shares the admin content database, so rollback should
        // not drop a table that may be owned by the admin application.
    }
};
