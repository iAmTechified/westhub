<?php

namespace Tests;

use App\Models\Setting;
use App\Support\SiteSettings;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function tearDown(): void
    {
        // Settings are memoised per request, and tests share a process.
        SiteSettings::flush();

        parent::tearDown();
    }

    /**
     * Write settings straight to the shared table, the same way the admin does.
     *
     * @param  array<string, string|null>  $values
     */
    protected function setSettings(string $group, array $values): void
    {
        foreach ($values as $key => $value) {
            Setting::query()->updateOrCreate(
                ['group' => $group, 'key' => $key],
                ['value' => $value, 'is_encrypted' => false, 'type' => 'string']
            );
        }

        SiteSettings::flush($group);
    }
}
