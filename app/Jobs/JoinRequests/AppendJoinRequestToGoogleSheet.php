<?php

namespace App\Jobs\JoinRequests;

use App\Services\JoinRequests\GoogleSheetsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AppendJoinRequestToGoogleSheet implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public function __construct(
        public array $payload,
    ) {
    }

    public function handle(GoogleSheetsService $sheets): void
    {
        if (! $sheets->isConfigured()) {
            return;
        }

        try {
            $sheets->appendJoinRequestRow($this->payload);
        } catch (\Throwable $exception) {
            Log::error('Join request Google Sheets append failed.', [
                'join_request_id' => data_get($this->payload, 'westhub_id'),
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}
