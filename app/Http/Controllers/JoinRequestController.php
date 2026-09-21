<?php

namespace App\Http\Controllers;

use App\Jobs\JoinRequests\AppendJoinRequestToGoogleSheet;
use App\Jobs\JoinRequests\SendJoinRequestInternalAlert;
use App\Jobs\JoinRequests\SendJoinRequestReceipt;
use App\Models\JoinRequest;
use App\Support\JoinRequests\JoinRequestSubmissionProjection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class JoinRequestController extends Controller
{
    public function create()
    {
        return view('pages.application-form');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'applicant_type' => ['required', 'in:skilled,non-skilled'],
            'title' => ['required', 'in:Mr.,Mrs.,Ms.,Miss,Dr.,Prof.,Rev.'],
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'middle_initial' => ['nullable', 'string', 'max:20'],
            'phone_country_code' => ['required', 'regex:/^\+\d{1,4}$/'],
            'phone_number' => ['required', 'string', 'max:40'],
            'email' => ['required', 'email', 'max:255'],
            'home_address' => ['required', 'string', 'max:255'],
            'position_applied_for' => ['required', 'string', 'max:180'],
            'date_available' => ['required', 'date_format:Y-m-d'],
            'desired_salary_hour' => ['required', 'numeric', 'min:0.01', 'max:10000'],

            'citizen_us' => ['required', 'in:yes,no'],
            'authorized_us' => ['required', 'in:yes,no'],
            'social_security' => ['required', 'string', 'max:80'],
            'date_of_birth' => ['required', 'date_format:Y-m-d', 'before:today'],
            'convicted_felony' => ['required', 'in:yes,no'],
            'felony_explanation' => ['nullable', 'required_if:convicted_felony,yes', 'string', 'max:500'],
            'worked_here' => ['required', 'in:yes,no'],
            'worked_here_when' => ['nullable', 'required_if:worked_here,yes', 'string', 'max:180'],

            'education' => ['required', 'array', 'size:2'],
            'education.*.school_name' => ['required', 'string', 'max:180'],
            'education.*.address' => ['required', 'string', 'max:255'],
            'education.*.degree' => ['required', 'string', 'max:180'],
            'education.*.from' => ['required', 'date_format:Y-m-d'],
            'education.*.to' => ['required', 'date_format:Y-m-d'],
            'education.*.graduated' => ['required', 'in:yes,no'],

            'military_branch' => ['nullable', 'string', 'max:180'],
            'military_rank' => ['nullable', 'string', 'max:180'],
            'military_from' => ['nullable', 'date_format:Y-m-d'],
            'military_to' => ['nullable', 'date_format:Y-m-d'],
            'military_discharge_type' => ['nullable', 'string', 'max:180'],

            'employers' => ['required', 'array', 'size:2'],
            'employers.*.company' => ['required', 'string', 'max:180'],
            'employers.*.phone_country_code' => ['required', 'regex:/^\+\d{1,4}$/'],
            'employers.*.phone_number' => ['required', 'string', 'max:40'],
            'employers.*.address' => ['required', 'string', 'max:255'],
            'employers.*.supervisor' => ['required', 'string', 'max:180'],
            'employers.*.job_title' => ['required', 'string', 'max:180'],
            'employers.*.responsibilities' => ['required', 'string', 'max:1000'],
            'employers.*.from' => ['required', 'date_format:Y-m-d'],
            'employers.*.to' => ['required', 'date_format:Y-m-d'],
            'employers.*.reason_for_leaving' => ['required', 'string', 'max:1000'],
            'employers.*.can_contact' => ['required', 'in:yes,no'],

            'references' => ['required', 'array', 'size:2'],
            'references.*.full_name' => ['required', 'string', 'max:180'],
            'references.*.relationship' => ['required', 'string', 'max:180'],
            'references.*.company' => ['required', 'string', 'max:180'],
            'references.*.phone_country_code' => ['required', 'regex:/^\+\d{1,4}$/'],
            'references.*.phone_number' => ['required', 'string', 'max:40'],
            'references.*.address' => ['required', 'string', 'max:255'],

            'signature_date' => ['required', 'date_format:Y-m-d'],
            'documents' => ['required', 'array', 'min:1', 'max:3'],
            'documents.*' => ['required', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png,webp', 'max:20480'],
            'signature_file' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'disclaimer_truth' => ['accepted'],
            'disclaimer_release' => ['accepted'],
        ], [
            'documents.required' => 'Upload at least one resume or supporting document.',
            'documents.*.max' => 'Each document must be 20MB or smaller.',
            'signature_file.max' => 'The signature image must be 5MB or smaller.',
            'signature_file.image' => 'The signature must be an image file.',
        ]);

        $documentPaths = collect($request->file('documents', []))
            ->map(fn ($file) => $file->store('join-requests/documents', 'public'))
            ->values()
            ->all();

        $signaturePath = $request->file('signature_file')->store('join-requests/signatures', 'public');
        $fullName = $this->fullName($validated);
        $professionalType = $validated['applicant_type'] === 'skilled'
            ? 'Skilled Professional'
            : 'Non-skilled Professional';

        $joinRequest = JoinRequest::create([
            'title' => $validated['title'],
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'middle_initial' => $validated['middle_initial'],
            'full_name' => $fullName,
            'email' => $validated['email'],
            'phone' => trim($validated['phone_country_code'].' '.$validated['phone_number']),
            'professional_type' => $validated['applicant_type'],
            'home_address' => $validated['home_address'],
            'position_applied_for' => $validated['position_applied_for'],
            'date_available' => $validated['date_available'],
            'desired_salary' => $validated['desired_salary_hour'],
            'is_citizen' => $validated['citizen_us'] === 'yes',
            'has_felony' => $validated['convicted_felony'] === 'yes',
            'felony_explanation' => $validated['felony_explanation'] ?? null,
            'is_authorized' => $validated['authorized_us'] === 'yes',
            'worked_here_before' => $validated['worked_here'] === 'yes',
            'worked_here_before_when' => $validated['worked_here_when'] ?? null,
            'ssn_tin' => $validated['social_security'],
            'dob' => $validated['date_of_birth'],
            'education' => $validated['education'],
            'military_service' => [
                'branch' => $validated['military_branch'] ?? null,
                'rank' => $validated['military_rank'] ?? null,
                'from' => $validated['military_from'] ?? null,
                'to' => $validated['military_to'] ?? null,
                'discharge_type' => $validated['military_discharge_type'] ?? null,
            ],
            'work_experience' => $validated['employers'],
            'references' => collect($validated['references'])->map(fn ($ref) => [
                'name' => $ref['full_name'],
                'relationship' => $ref['relationship'],
                'company' => $ref['company'],
                'phone' => trim($ref['phone_country_code'].' '.$ref['phone_number']),
                'address' => $ref['address'],
            ])->all(),
            'profession' => $professionalType.' - '.$validated['position_applied_for'],
            'about' => $this->buildSummary($validated, $documentPaths, $signaturePath),
            'qualifications' => $this->buildQualifications($validated, $documentPaths, $signaturePath, $professionalType),
            'resume_path' => $documentPaths[0] ?? null,
            'document_paths' => $documentPaths,
            'signature_path' => $signaturePath,
            'disclaimer_accepted' => true,
            'status' => 'new',
        ]);

        $projection = JoinRequestSubmissionProjection::fromValidated($joinRequest, $validated);
        $this->syncToSheetNowOrQueue($projection->toArray());
        $this->dispatchSubmissionJob(new SendJoinRequestReceipt($projection->toArray()));
        $this->dispatchSubmissionJob(new SendJoinRequestInternalAlert($projection->toArray()));

        return response()->json([
            'message' => 'Thank you. Your application has been submitted.',
        ]);
    }

    /**
     * Append the row during this request, falling back to the queue when
     * Google is slow or unreachable, so the sheet keeps up even where no
     * queue worker runs. The application row is already saved regardless.
     */
    private function syncToSheetNowOrQueue(array $payload): void
    {
        try {
            AppendJoinRequestToGoogleSheet::dispatchSync($payload, true);

            return;
        } catch (Throwable $exception) {
            report($exception);
        }

        $this->dispatchSubmissionJob(new AppendJoinRequestToGoogleSheet($payload));
    }

    private function dispatchSubmissionJob(object $job): void
    {
        try {
            dispatch($job);
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    private function fullName(array $data): string
    {
        return trim(collect([
            $data['title'] ?? null,
            $data['first_name'] ?? null,
            $data['middle_initial'] ?? null,
            $data['last_name'] ?? null,
        ])->filter()->implode(' '));
    }

    private function buildSummary(array $data, array $documentPaths, string $signaturePath): string
    {
        $lines = [
            'Applicant type: '.$this->professionalType($data['applicant_type']),
            'Position applied for: '.$data['position_applied_for'],
            'Home address: '.$data['home_address'],
            'Date available: '.$data['date_available'],
            'Desired salary/hour: $'.$data['desired_salary_hour'],
            'U.S. citizen: '.$this->yesNo($data['citizen_us']),
            'Authorized to work in the U.S.: '.$this->yesNo($data['authorized_us']),
            'Date of birth: '.$data['date_of_birth'],
            'Convicted of a felony: '.$this->yesNo($data['convicted_felony']),
            'Felony explanation: '.($data['felony_explanation'] ?? 'N/A'),
            'Previously worked here: '.$this->yesNo($data['worked_here']),
            'Previous company date: '.($data['worked_here_when'] ?? 'N/A'),
            'Social Security/TIN: '.$data['social_security'],
            'Military branch: '.($data['military_branch'] ?? 'N/A'),
            'Military rank at discharge: '.($data['military_rank'] ?? 'N/A'),
            'Military service: '.(($data['military_from'] ?? 'N/A').' to '.($data['military_to'] ?? 'N/A')),
            'Military discharge type: '.($data['military_discharge_type'] ?? 'N/A'),
            'Uploaded documents: '.implode(', ', $documentPaths),
            'Signature image: '.$signaturePath,
            'Signature date: '.$data['signature_date'],
        ];

        foreach ($data['education'] as $index => $education) {
            $lines[] = sprintf(
                'Education %d: %s, %s, %s, %s to %s, graduated: %s',
                $index + 1,
                $education['school_name'],
                $education['address'],
                $education['degree'],
                $education['from'],
                $education['to'],
                $this->yesNo($education['graduated'])
            );
        }

        foreach ($data['employers'] as $index => $employer) {
            $lines[] = sprintf(
                'Previous employer %d: %s, %s %s, %s, supervisor: %s, job title: %s, %s to %s, responsibilities: %s, reason for leaving: %s, may contact: %s',
                $index + 1,
                $employer['company'],
                $employer['phone_country_code'],
                $employer['phone_number'],
                $employer['address'],
                $employer['supervisor'],
                $employer['job_title'],
                $employer['from'],
                $employer['to'],
                $employer['responsibilities'],
                $employer['reason_for_leaving'],
                $this->yesNo($employer['can_contact'])
            );
        }

        foreach ($data['references'] as $index => $reference) {
            $lines[] = sprintf(
                'Reference %d: %s, %s, %s, %s %s, %s',
                $index + 1,
                $reference['full_name'],
                $reference['relationship'],
                $reference['company'],
                $reference['phone_country_code'],
                $reference['phone_number'],
                $reference['address']
            );
        }

        return implode(PHP_EOL, $lines);
    }

    private function buildQualifications(array $data, array $documentPaths, string $signaturePath, string $professionalType): array
    {
        $items = [
            'Applicant type: '.$professionalType,
            'Position applied for: '.$data['position_applied_for'],
            'Date available: '.$data['date_available'],
            'Desired salary/hour: $'.$data['desired_salary_hour'],
        ];

        foreach ($data['education'] as $index => $education) {
            $items[] = sprintf(
                'Education %d: %s, %s',
                $index + 1,
                $education['school_name'],
                $education['degree']
            );
        }

        foreach ($data['references'] as $index => $reference) {
            $items[] = sprintf(
                'Reference %d: %s (%s)',
                $index + 1,
                $reference['full_name'],
                $reference['relationship']
            );
        }

        $items[] = 'Documents: '.implode(', ', $documentPaths);
        $items[] = 'Signature: '.$signaturePath;

        return $items;
    }

    private function professionalType(string $type): string
    {
        return $type === 'skilled' ? 'Skilled Professional' : 'Non-skilled Professional';
    }

    private function yesNo(string $value): string
    {
        return $value === 'yes' ? 'Yes' : 'No';
    }
}
