<?php

namespace App\Support\JoinRequests;

use App\Models\JoinRequest;

class JoinRequestSubmissionProjection
{
    public function __construct(
        public readonly int $westhubId,
        public readonly string $submittedAt,
        public readonly string $fullName,
        public readonly string $email,
        public readonly string $phone,
        public readonly string $applicantType,
        public readonly string $positionProfession,
        public readonly string $dateAvailable,
        public readonly string $sheetStage,
        public readonly string $westhubDecision,
        public readonly string $owner,
        public readonly string $notes,
        public readonly string $adminUrl,
        public readonly string $resumeUrl,
        public readonly string $signatureUrl,
        public readonly array $documentUrls,
    ) {
    }

    public static function fromValidated(JoinRequest $joinRequest, array $validated): self
    {
        $baseUrl = rtrim((string) config('services.westhub_admin.base_url', config('app.url')), '/');

        return new self(
            westhubId: (int) $joinRequest->id,
            submittedAt: $joinRequest->created_at?->toIso8601String() ?? now()->toIso8601String(),
            fullName: (string) $joinRequest->full_name,
            email: (string) $joinRequest->email,
            phone: (string) ($joinRequest->phone ?? ''),
            applicantType: self::professionalTypeLabel((string) $validated['applicant_type']),
            positionProfession: (string) ($joinRequest->profession ?? $validated['position_applied_for']),
            dateAvailable: (string) $validated['date_available'],
            sheetStage: 'new',
            westhubDecision: '',
            owner: '',
            notes: (string) ($joinRequest->about ?? ''),
            adminUrl: $baseUrl === '' ? '' : $baseUrl.'/admin/join-requests',
            resumeUrl: $joinRequest->resume_path ? $baseUrl.'/storage/'.$joinRequest->resume_path : '',
            signatureUrl: $joinRequest->signature_path ? $baseUrl.'/storage/'.$joinRequest->signature_path : '',
            documentUrls: collect($joinRequest->document_paths ?? [])->map(fn($p) => $baseUrl.'/storage/'.$p)->all(),
        );
    }

    public function toArray(): array
    {
        return [
            'westhub_id' => $this->westhubId,
            'submitted_at' => $this->submittedAt,
            'full_name' => $this->fullName,
            'email' => $this->email,
            'phone' => $this->phone,
            'applicant_type' => $this->applicantType,
            'position_profession' => $this->positionProfession,
            'date_available' => $this->dateAvailable,
            'sheet_stage' => $this->sheetStage,
            'westhub_decision' => $this->westhubDecision,
            'owner' => $this->owner,
            'notes' => $this->notes,
            'admin_url' => $this->adminUrl,
            'resume_url' => $this->resumeUrl,
            'signature_url' => $this->signatureUrl,
            'document_urls' => $this->documentUrls,
        ];
    }

    private static function professionalTypeLabel(string $value): string
    {
        return $value === 'skilled' ? 'Skilled Professional' : 'Non-skilled Professional';
    }
}
