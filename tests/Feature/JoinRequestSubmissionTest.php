<?php

namespace Tests\Feature;

use App\Jobs\JoinRequests\AppendJoinRequestToGoogleSheet;
use App\Jobs\JoinRequests\SendJoinRequestInternalAlert;
use App\Jobs\JoinRequests\SendJoinRequestReceipt;
use App\Models\JoinRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class JoinRequestSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_submission_stores_the_join_request_and_dispatches_stopgap_jobs(): void
    {
        Bus::fake();
        Storage::fake('public');

        $response = $this->post(route('join-requests.store'), $this->payload(), [
            'Accept' => 'application/json',
        ]);

        $response->assertOk()->assertJson([
            'message' => 'Thank you. Your application has been submitted.',
        ]);

        $request = JoinRequest::query()->firstOrFail();

        $this->assertSame('new', $request->status);
        $this->assertSame('Mr. Alex J Carter', $request->full_name);
        $this->assertSame('alex@example.com', $request->email);
        $this->assertStringContainsString('Social Security/TIN: 123-45-6789', (string) $request->about);

        Bus::assertDispatched(AppendJoinRequestToGoogleSheet::class, fn (AppendJoinRequestToGoogleSheet $job): bool => $job->payload['westhub_id'] === $request->id);
        Bus::assertDispatched(SendJoinRequestReceipt::class, fn (SendJoinRequestReceipt $job): bool => $job->payload['email'] === 'alex@example.com');
        Bus::assertDispatched(SendJoinRequestInternalAlert::class, fn (SendJoinRequestInternalAlert $job): bool => $job->payload['position_profession'] === 'Skilled Professional - Registered Nurse');
    }

    private function payload(): array
    {
        return [
            'applicant_type' => 'skilled',
            'title' => 'Mr.',
            'first_name' => 'Alex',
            'last_name' => 'Carter',
            'middle_initial' => 'J',
            'phone_country_code' => '+1',
            'phone_number' => '2015550122',
            'email' => 'alex@example.com',
            'home_address' => '123 Main St',
            'position_applied_for' => 'Registered Nurse',
            'date_available' => '2026-05-15',
            'desired_salary_hour' => '35.50',
            'citizen_us' => 'yes',
            'authorized_us' => 'yes',
            'social_security' => '123-45-6789',
            'date_of_birth' => '1990-01-01',
            'convicted_felony' => 'no',
            'felony_explanation' => '',
            'worked_here' => 'no',
            'worked_here_when' => '',
            'education' => [
                [
                    'school_name' => 'State University',
                    'address' => '1 College Way',
                    'degree' => 'BSN',
                    'from' => '2008-09-01',
                    'to' => '2012-05-30',
                    'graduated' => 'yes',
                ],
                [
                    'school_name' => 'City College',
                    'address' => '2 Learning Ave',
                    'degree' => 'ASN',
                    'from' => '2006-09-01',
                    'to' => '2008-05-30',
                    'graduated' => 'yes',
                ],
            ],
            'military_branch' => '',
            'military_rank' => '',
            'military_from' => '',
            'military_to' => '',
            'military_discharge_type' => '',
            'employers' => [
                [
                    'company' => 'Hope Clinic',
                    'phone_country_code' => '+1',
                    'phone_number' => '3125550101',
                    'address' => '10 Care St',
                    'supervisor' => 'Jamie Lewis',
                    'job_title' => 'Nurse',
                    'responsibilities' => 'Patient support and intake',
                    'from' => '2021-01-01',
                    'to' => '2024-01-01',
                    'reason_for_leaving' => 'Relocation',
                    'can_contact' => 'yes',
                ],
                [
                    'company' => 'Health Center',
                    'phone_country_code' => '+1',
                    'phone_number' => '7735550102',
                    'address' => '20 Wellness Rd',
                    'supervisor' => 'Taylor Brooks',
                    'job_title' => 'Assistant Nurse',
                    'responsibilities' => 'Charting and visits',
                    'from' => '2018-01-01',
                    'to' => '2020-12-31',
                    'reason_for_leaving' => 'Career growth',
                    'can_contact' => 'yes',
                ],
            ],
            'references' => [
                [
                    'full_name' => 'Jordan Price',
                    'relationship' => 'Supervisor',
                    'company' => 'Hope Clinic',
                    'phone_country_code' => '+1',
                    'phone_number' => '3125550103',
                    'address' => '10 Care St',
                ],
                [
                    'full_name' => 'Casey Nguyen',
                    'relationship' => 'Mentor',
                    'company' => 'Health Center',
                    'phone_country_code' => '+1',
                    'phone_number' => '7735550104',
                    'address' => '20 Wellness Rd',
                ],
            ],
            'signature_date' => '2026-04-25',
            'documents' => [
                UploadedFile::fake()->create('resume.pdf', 200, 'application/pdf'),
            ],
            'signature_file' => UploadedFile::fake()->image('signature.png'),
            'disclaimer_truth' => '1',
            'disclaimer_release' => '1',
        ];
    }
}
