<?php

namespace App\Services\Google;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Client for the WestHub Apps Script web app bound to the spreadsheet
 * (docs/google-apps-script/westhub-sheets.gs).
 *
 * This is the no-service-account route into Sheets: the script runs as the
 * spreadsheet's owner, so there is no Google Cloud key to create and nothing to
 * share. It is only ever called from queued jobs on the server, never from the
 * browser, so the web app URL and the shared secret are not exposed to
 * visitors, and every submission is still saved to the database first.
 *
 * The script mirrors GoogleSheets' behaviour: rows are matched to headers by
 * name, missing tabs and columns are created, and values are stored as literal
 * text so a leading "=" can never run as a formula.
 */
class AppsScriptSheets
{
    /** A deployed web app URL. /dev URLs only work for a signed-in editor. */
    public const URL_PATTERN = '#^https://script\.google\.com/macros/s/[A-Za-z0-9_-]+/exec$#';

    public function __construct(
        protected string $url,
        protected string $secret,
        protected int $timeout = 30,
    ) {
    }

    public static function looksLikeWebAppUrl(string $url): bool
    {
        return (bool) preg_match(self::URL_PATTERN, $url);
    }

    /**
     * @param  array<int, string>  $headerContract
     * @param  array<string, string|null>  $values  header name => cell value
     */
    public function append(string $tab, array $headerContract, array $values): void
    {
        $this->call('append', [
            'tab' => $tab,
            'headers' => array_values($headerContract),
            'values' => (object) array_map(static fn ($value): string => (string) ($value ?? ''), $values),
        ]);
    }

    /**
     * Update one named column on the row whose first column matches $key.
     * Returns false when no such row exists.
     *
     * @param  array<int, string>  $headerContract
     */
    public function update(string $tab, array $headerContract, string $key, string $header, ?string $value): bool
    {
        $result = $this->call('update', [
            'tab' => $tab,
            'headers' => array_values($headerContract),
            'key' => $key,
            'header' => $header,
            'value' => (string) ($value ?? ''),
        ]);

        return (bool) ($result['updated'] ?? false);
    }

    /**
     * @return array{title: string, tabs: array<int, string>}
     */
    public function ping(): array
    {
        $result = $this->call('ping');

        return [
            'title' => (string) ($result['title'] ?? 'Untitled'),
            'tabs' => array_map('strval', (array) ($result['tabs'] ?? [])),
        ];
    }

    /**
     * Apps Script always answers HTTP 200, even for errors, so success is read
     * from the JSON body. POSTs are answered with a 302 to a one-off result
     * URL; the HTTP client follows it as a GET, which is what Google expects.
     *
     * @return array<string, mixed>
     */
    protected function call(string $action, array $payload = []): array
    {
        try {
            $response = Http::acceptJson()
                ->timeout($this->timeout)
                ->post($this->url, ['secret' => $this->secret, 'action' => $action] + $payload);
        } catch (ConnectionException $e) {
            // The cURL text (certificates, timeouts, Google's one-off redirect
            // URL) stays on the exception for logs and debug mode.
            throw new RuntimeException('Could not reach Google Apps Script from this server. Try again in a minute; if it keeps failing, the server cannot make secure outgoing connections.', 0, $e);
        }

        if ($response->failed()) {
            throw new RuntimeException('The Apps Script web app returned HTTP ' . $response->status() . '. Check the URL is the current deployment.');
        }

        $json = $response->json();

        if (! is_array($json)) {
            // Google serves a sign-in page instead of running the script when
            // the deployment is not open to "Anyone".
            throw new RuntimeException('The Apps Script web app did not answer with JSON. Redeploy it with "Execute as: Me" and "Who has access: Anyone", and use the URL ending in /exec.');
        }

        if (($json['ok'] ?? false) !== true) {
            throw new RuntimeException('Apps Script: ' . ($json['error'] ?? 'the script reported an unknown error.'));
        }

        return $json;
    }
}
