<?php

use App\Models\JoinRequest;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('join_requests')
            ->whereNull('professional_type')
            ->orWhereRaw('TRIM(professional_type) = ? ', [''])
            ->orWhereRaw('LOWER(REPLACE(REPLACE(professional_type, "_", "-"), " ", "-")) NOT IN (?, ?)', [
                JoinRequest::ROLE_SKILLED,
                JoinRequest::ROLE_NON_SKILLED,
            ])
            ->update(['professional_type' => JoinRequest::ROLE_NON_SKILLED]);
    }

    public function down(): void
    {
        // Intentionally no rollback because original role types are not recoverable.
    }
};
