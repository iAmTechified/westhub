<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('join_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('join_requests', 'document_paths')) {
                $table->json('document_paths')->nullable()->after('resume_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('join_requests', function (Blueprint $table) {
            $table->dropColumn('document_paths');
        });
    }
};
