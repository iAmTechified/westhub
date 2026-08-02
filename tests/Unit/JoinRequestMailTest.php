<?php

namespace Tests\Unit;

use App\Mail\JoinRequestInternalAlertMail;
use App\Mail\JoinRequestReceiptMail;
use Tests\TestCase;

class JoinRequestMailTest extends TestCase
{
    public function test_receipt_email_is_generic_and_does_not_echo_sensitive_fields(): void
    {
        $html = (new JoinRequestReceiptMail($this->payload()))->render();

        $this->assertStringContainsString('We have received your application for the WestHub Healthcare team.', $html);
        $this->assertStringNotContainsString('Social Security', $html);
        $this->assertStringNotContainsString('Date of birth', $html);
        $this->assertStringNotContainsString('Home address', $html);
    }

    public function test_internal_alert_email_contains_safe_summary_and_admin_link_only(): void
    {
        $html = (new JoinRequestInternalAlertMail($this->payload()))->render();

        $this->assertStringContainsString('WestHub ID', $html);
        $this->assertStringContainsString('Mr. Alex Carter', $html);
        $this->assertStringContainsString('Skilled Professional - Registered Nurse', $html);
        $this->assertStringContainsString('https://admin.westhub.test/admin/join-requests', $html);
        $this->assertStringNotContainsString('123-45-6789', $html);
        $this->assertStringNotContainsString('Date of birth', $html);
        $this->assertStringNotContainsString('Reference 1', $html);
    }

    private function payload(): array
    {
        return [
            'westhub_id' => 42,
            'submitted_at' => '2026-04-25T08:15:00+00:00',
            'full_name' => 'Mr. Alex Carter',
            'email' => 'alex@example.com',
            'phone' => '+1 2015550122',
            'applicant_type' => 'Skilled Professional',
            'position_profession' => 'Skilled Professional - Registered Nurse',
            'date_available' => '2026-05-15',
            'sheet_stage' => 'new',
            'westhub_decision' => '',
            'owner' => '',
            'notes' => '',
            'admin_url' => 'https://admin.westhub.test/admin/join-requests',
        ];
    }
}
