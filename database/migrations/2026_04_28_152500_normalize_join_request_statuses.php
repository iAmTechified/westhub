<?php

use App\Models\JoinRequest;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('join_requests')
            ->whereNotIn('status', [
                JoinRequest::STATUS_NEW,
                JoinRequest::STATUS_ACCEPTED,
                JoinRequest::STATUS_DECLINED,
            ])
            ->update(['status' => JoinRequest::STATUS_NEW]);
    }

    public function down(): void
    {
        // Intentionally left blank because we cannot safely reconstruct legacy statuses.
    }
};
