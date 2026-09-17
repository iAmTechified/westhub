<?php

namespace App\Support\Sheets;

/**
 * The column contract for the Join Requests tab.
 *
 * These are the same 16 headers the previous implementation used (the original
 * 13 plus resume_url, signature_url and document_urls), so existing spreadsheets
 * keep working. Rows are now matched by header NAME, so reordering or adding
 * columns in the sheet no longer breaks the sync.
 */
class JoinRequestSheet
{
    public const TAB_KEY = 'join_requests';
    public const TAB_DEFAULT = 'Join Requests';

    public const HEADERS = [
        'westhub_id',
        'submitted_at',
        'full_name',
        'email',
        'phone',
        'applicant_type',
        'position/profession',
        'date_available',
        'sheet_stage',
        'westhub_decision',
        'owner',
        'notes',
        'admin_url',
        'resume_url',
        'signature_url',
        'document_urls',
    ];

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, string>
     */
    public static function row(array $payload): array
    {
        return [
            'westhub_id' => (string) data_get($payload, 'westhub_id', ''),
            'submitted_at' => (string) data_get($payload, 'submitted_at', ''),
            'full_name' => (string) data_get($payload, 'full_name', ''),
            'email' => (string) data_get($payload, 'email', ''),
            'phone' => (string) data_get($payload, 'phone', ''),
            'applicant_type' => (string) data_get($payload, 'applicant_type', ''),
            'position/profession' => (string) data_get($payload, 'position_profession', ''),
            'date_available' => (string) data_get($payload, 'date_available', ''),
            'sheet_stage' => (string) data_get($payload, 'sheet_stage', ''),
            'westhub_decision' => (string) data_get($payload, 'westhub_decision', ''),
            'owner' => (string) data_get($payload, 'owner', ''),
            'notes' => (string) data_get($payload, 'notes', ''),
            'admin_url' => (string) data_get($payload, 'admin_url', ''),
            'resume_url' => (string) data_get($payload, 'resume_url', ''),
            'signature_url' => (string) data_get($payload, 'signature_url', ''),
            'document_urls' => implode("\n", (array) data_get($payload, 'document_urls', [])),
        ];
    }
}
