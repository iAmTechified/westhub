<?php

namespace App\Services\JoinRequests;

use App\Services\Google\GoogleSheets;
use App\Support\Sheets\JoinRequestSheet;

/**
 * Join-request Sheets sync.
 *
 * A thin wrapper over the shared GoogleSheets client so the credentials,
 * spreadsheet and tab name come from the admin settings (falling back to env)
 * and can be changed without a deploy. The public API used by the jobs
 * (isConfigured, appendJoinRequestRow, updateWesthubDecision) is unchanged.
 */
class GoogleSheetsService
{
    /** Kept for backwards compatibility with existing callers and tests. */
    public const HEADERS = JoinRequestSheet::HEADERS;

    public function __construct(
        protected GoogleSheets $sheets,
    ) {
    }

    public function isConfigured(): bool
    {
        return $this->sheets->isEnabled();
    }

    public function appendJoinRequestRow(array $payload): void
    {
        $this->sheets->appendRow(
            $this->tab(),
            JoinRequestSheet::HEADERS,
            JoinRequestSheet::row($payload)
        );
    }

    public function updateWesthubDecision(int $westhubId, string $decision): bool
    {
        return $this->sheets->updateRowColumn(
            $this->tab(),
            JoinRequestSheet::HEADERS,
            (string) $westhubId,
            'westhub_decision',
            $decision
        );
    }

    /**
     * Also keep the pipeline column in step, which the previous version never
     * did after the first write.
     */
    public function updateSheetStage(int $westhubId, string $stage): bool
    {
        return $this->sheets->updateRowColumn(
            $this->tab(),
            JoinRequestSheet::HEADERS,
            (string) $westhubId,
            'sheet_stage',
            $stage
        );
    }

    protected function tab(): string
    {
        return $this->sheets->tab(
            JoinRequestSheet::TAB_KEY,
            (string) (config('services.google_sheets.sheet_name') ?: JoinRequestSheet::TAB_DEFAULT)
        );
    }
}
