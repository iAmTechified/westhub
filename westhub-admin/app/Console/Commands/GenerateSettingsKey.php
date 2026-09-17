<?php

namespace App\Console\Commands;

use App\Support\SettingsCrypto;
use Illuminate\Console\Command;

class GenerateSettingsKey extends Command
{
    protected $signature = 'westhub:settings-key';

    protected $description = 'Generate a SETTINGS_ENCRYPTION_KEY to share between the public site and westhub-admin';

    public function handle(): int
    {
        if (SettingsCrypto::sharedKeyConfigured()) {
            $this->warn('SETTINGS_ENCRYPTION_KEY is already set in this app.');
            $this->line('Make sure the other app uses the exact same value.');
            $this->newLine();
        }

        $key = 'base64:'.base64_encode(random_bytes(32));

        $this->info('Add this line to the .env of BOTH apps (public site and westhub-admin):');
        $this->newLine();
        $this->line('SETTINGS_ENCRYPTION_KEY='.$key);
        $this->newLine();
        $this->comment('Both apps must use the same value, otherwise encrypted settings saved in the admin cannot be read by the public site.');

        return self::SUCCESS;
    }
}
