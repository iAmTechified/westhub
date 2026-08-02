<?php

namespace App\Jobs\JoinRequests;

use App\Models\JoinRequest;
use App\Services\JoinRequests\GoogleSheetsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncJoinRequestDecisionToGoogleSheet implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public function __construct(
        public int $joinRequestId,
    ) {
    }

    public function handle(GoogleSheetsService $sheets): void
    {
        if (! $sheets->isConfigured()) {
            return;
        }

        $joinRequest = JoinRequest::query()->find($this->joinRequestId);
        if (! $joinRequest || ! in_array($joinRequest->status, [
            JoinRequest::STATUS_ACCEPTED,
            JoinRequest::STATUS_DECLINED,
        ], true)) {
            return;
        }

        try {
            $updated = $sheets->updateWesthubDecision($joinRequest->id, $joinRequest->status);

            if (! $updated) {
                Log::warning('Join request decision sync skipped because the Google Sheets row was not found.', [
                    'join_request_id' => $joinRequest->id,
                    'status' => $joinRequest->status,
                ]);
            }
        } catch (\Throwable $exception) {
            Log::error('Join request Google Sheets decision sync failed.', [
                'join_request_id' => $this->joinRequestId,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}
