<?php

namespace App\Services\Google;

use App\Support\SiteSettings;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

/**
 * Settings-driven Google Sheets client.
 *
 * Everything it needs (spreadsheet id, tab names, service account) is read from
 * the shared settings table, so an operator can paste credentials into the
 * admin and the very next submission starts syncing. No deploy, no env edit.
 *
 * Header handling is deliberately forgiving: rows are written by header NAME
 * rather than fixed position, and columns a human removed are re-appended
 * instead of failing every submission from then on.
 */
class GoogleSheets
{
    public const SCOPE = 'https://www.googleapis.com/auth/spreadsheets';

    /** @var array<string, array<string, int>> tab => header => column index */
    protected array $headerMaps = [];

    /**
     * Syncing is on when the admin toggle says so. If the toggle has never been
     * saved, fall back to "configured" so env-only setups that synced before
     * the toggle existed keep syncing after deploy.
     */
    public function isEnabled(): bool
    {
        $integrations = SiteSettings::group('integrations');

        // A saved row always wins, including an empty value, which reads as off.
        if (array_key_exists('google_sheets_enabled', $integrations)) {
            return SiteSettings::bool('integrations', 'google_sheets_enabled', false) && $this->isConfigured();
        }

        return $this->isConfigured();
    }

    public function isConfigured(): bool
    {
        return $this->spreadsheetId() !== '' && $this->account()->isUsable();
    }

    public function spreadsheetId(): string
    {
        return trim((string) SiteSettings::get(
            'integrations',
            'google_sheets_spreadsheet_id',
            config('services.google_sheets.spreadsheet_id')
        ));
    }

    public function tab(string $key, string $default): string
    {
        $name = trim((string) SiteSettings::get('integrations', 'google_sheets_' . $key . '_tab', $default));

        return $name !== '' ? $name : $default;
    }

    public function account(): GoogleServiceAccount
    {
        return GoogleServiceAccount::fromSecret(
            SiteSettings::get('integrations', 'google_sheets_service_account_email', config('services.google_sheets.service_account_email')),
            SiteSettings::get('integrations', 'google_sheets_private_key', config('services.google_sheets.private_key')),
        );
    }

    /**
     * Append one row, positioning each value under its header by name.
     *
     * @param  array<int, string>  $headerContract
     * @param  array<string, string|null>  $values  header name => cell value
     */
    public function appendRow(string $tab, array $headerContract, array $values): void
    {
        $map = $this->headerMap($tab, $headerContract);
        $width = max($map) + 1;
        $row = array_fill(0, $width, '');

        foreach ($values as $header => $value) {
            if (array_key_exists($header, $map)) {
                $row[$map[$header]] = (string) ($value ?? '');
            }
        }

        // valueInputOption=RAW stops Sheets evaluating a leading "=" or "+" in
        // user-supplied text as a formula.
        $response = $this->request()->post(
            $this->valuesUrl($tab . '!A:' . $this->columnLetter($width - 1)) . ':append?valueInputOption=RAW&insertDataOption=INSERT_ROWS',
            ['values' => [$row]]
        );

        if ($response->failed()) {
            throw new RuntimeException('Google Sheets append failed: ' . $response->body());
        }
    }

    /**
     * Update a single named column on the row whose first column matches $key.
     * Returns false when no such row exists.
     *
     * @param  array<int, string>  $headerContract
     */
    public function updateRowColumn(string $tab, array $headerContract, string $key, string $header, ?string $value): bool
    {
        $rowNumber = $this->findRowNumber($tab, $key);

        if ($rowNumber === null) {
            return false;
        }

        $map = $this->headerMap($tab, $headerContract);

        if (! array_key_exists($header, $map)) {
            return false;
        }

        $cell = $this->columnLetter($map[$header]) . $rowNumber;

        $response = $this->request()->put(
            $this->valuesUrl($tab . '!' . $cell) . '?valueInputOption=RAW',
            ['values' => [[(string) ($value ?? '')]]]
        );

        if ($response->failed()) {
            throw new RuntimeException('Google Sheets update failed: ' . $response->body());
        }

        return true;
    }

    public function findRowNumber(string $tab, string $key): ?int
    {
        $response = $this->request()->get($this->valuesUrl($tab . '!A:A'));

        if ($response->failed()) {
            throw new RuntimeException('Google Sheets lookup failed: ' . $response->body());
        }

        foreach ((array) $response->json('values', []) as $index => $row) {
            if ((string) ($row[0] ?? '') === $key) {
                return $index + 1;
            }
        }

        return null;
    }

    /**
     * Resolve header name => column index for a tab, writing the header row when
     * the tab is empty and re-appending any contract column that has gone
     * missing.
     *
     * @param  array<int, string>  $headerContract
     * @return array<string, int>
     */
    public function headerMap(string $tab, array $headerContract): array
    {
        if (isset($this->headerMaps[$tab])) {
            return $this->headerMaps[$tab];
        }

        $this->ensureTabExists($tab);

        $response = $this->request()->get($this->valuesUrl($tab . '!1:1'));

        if ($response->failed()) {
            throw new RuntimeException('Google Sheets header read failed: ' . $response->body());
        }

        $existing = array_map(
            static fn ($value): string => trim((string) $value),
            (array) $response->json('values.0', [])
        );

        if (array_filter($existing) === []) {
            $write = $this->request()->put(
                $this->valuesUrl($tab . '!A1:' . $this->columnLetter(count($headerContract) - 1) . '1') . '?valueInputOption=RAW',
                ['values' => [array_values($headerContract)]]
            );

            if ($write->failed()) {
                throw new RuntimeException('Google Sheets header write failed: ' . $write->body());
            }

            $existing = array_values($headerContract);
        }

        $map = [];

        foreach ($existing as $index => $header) {
            if ($header !== '' && ! array_key_exists($header, $map)) {
                $map[$header] = $index;
            }
        }

        $missing = array_values(array_diff($headerContract, array_keys($map)));

        if ($missing !== []) {
            $startIndex = count($existing);

            $append = $this->request()->put(
                $this->valuesUrl($tab . '!' . $this->columnLetter($startIndex) . '1:' . $this->columnLetter($startIndex + count($missing) - 1) . '1') . '?valueInputOption=RAW',
                ['values' => [$missing]]
            );

            if ($append->failed()) {
                throw new RuntimeException('Google Sheets header repair failed: ' . $append->body());
            }

            foreach ($missing as $offset => $header) {
                $map[$header] = $startIndex + $offset;
            }
        }

        return $this->headerMaps[$tab] = $map;
    }

    /** Create the worksheet tab if the spreadsheet does not have it yet. */
    public function ensureTabExists(string $tab): void
    {
        $meta = $this->request()->get(
            'https://sheets.googleapis.com/v4/spreadsheets/' . rawurlencode($this->spreadsheetId()) . '?fields=sheets.properties.title'
        );

        if ($meta->failed()) {
            throw new RuntimeException('Google Sheets metadata read failed: ' . $meta->body());
        }

        $titles = array_map(
            static fn ($sheet): string => (string) data_get($sheet, 'properties.title'),
            (array) $meta->json('sheets', [])
        );

        if (in_array($tab, $titles, true)) {
            return;
        }

        $create = $this->request()->post(
            'https://sheets.googleapis.com/v4/spreadsheets/' . rawurlencode($this->spreadsheetId()) . ':batchUpdate',
            ['requests' => [['addSheet' => ['properties' => ['title' => $tab]]]]]
        );

        if ($create->failed()) {
            throw new RuntimeException('Could not create the "' . $tab . '" tab: ' . $create->body());
        }
    }

    /**
     * Backs the admin "Test connection" button.
     *
     * @return array{ok: bool, message: string}
     */
    public function testConnection(): array
    {
        if ($this->spreadsheetId() === '') {
            return ['ok' => false, 'message' => 'No spreadsheet ID has been set.'];
        }

        if (! $this->account()->isUsable()) {
            return ['ok' => false, 'message' => 'Service account email or private key is missing.'];
        }

        try {
            $this->account()->forgetToken(self::SCOPE);

            $response = $this->request()->get(
                'https://sheets.googleapis.com/v4/spreadsheets/' . rawurlencode($this->spreadsheetId()) . '?fields=properties.title,sheets.properties.title'
            );

            if ($response->status() === 403) {
                return ['ok' => false, 'message' => 'Access denied. Share the spreadsheet with ' . $this->account()->clientEmail() . ' and give it Editor access.'];
            }

            if ($response->status() === 404) {
                return ['ok' => false, 'message' => 'Spreadsheet not found. Check the ID copied from the sheet URL.'];
            }

            if ($response->failed()) {
                return ['ok' => false, 'message' => 'Google rejected the request: ' . $response->body()];
            }

            $title = (string) $response->json('properties.title', 'Untitled');
            $tabs = array_map(static fn ($s): string => (string) data_get($s, 'properties.title'), (array) $response->json('sheets', []));

            return ['ok' => true, 'message' => 'Connected to "' . $title . '". Tabs found: ' . (implode(', ', $tabs) ?: 'none') . '.'];
        } catch (Throwable $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    protected function request(): PendingRequest
    {
        return Http::withToken($this->account()->accessToken(self::SCOPE))
            ->acceptJson()
            ->timeout(20);
    }

    protected function valuesUrl(string $range): string
    {
        return sprintf(
            'https://sheets.googleapis.com/v4/spreadsheets/%s/values/%s',
            rawurlencode($this->spreadsheetId()),
            rawurlencode($range),
        );
    }

    protected function columnLetter(int $index): string
    {
        $letter = '';

        for ($i = max(0, $index); ; $i = intdiv($i, 26) - 1) {
            $letter = chr(65 + ($i % 26)) . $letter;

            if ($i < 26) {
                break;
            }
        }

        return $letter;
    }
}
