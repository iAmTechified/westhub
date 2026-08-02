<?php

namespace Database\Seeders;

use App\Models\JoinRequest;
use App\Models\User;
use Illuminate\Database\Seeder;

class JoinRequestSeeder extends Seeder
{
    public function run(): void
    {
        $reviewerId = User::query()->value('id');
        $professions = [
            'Nurse',
            'Care Assistant',
            'Support Worker',
            'Physiotherapist',
            'Occupational Therapist',
            'Social Worker',
            'Healthcare Assistant',
            'Community Carer',
        ];
        $roleTypes = [JoinRequest::ROLE_SKILLED, JoinRequest::ROLE_NON_SKILLED];

        if (JoinRequest::query()->exists()) {
            return;
        }

        for ($i = 1; $i <= 36; $i++) {
            $status = $this->pickStatus($i);
            $createdAt = now()->subDays(fake()->numberBetween(0, 60))->subHours(fake()->numberBetween(0, 23));
            $isReviewed = in_array($status, [JoinRequest::STATUS_ACCEPTED, JoinRequest::STATUS_DECLINED], true);
            $isDecided = in_array($status, [JoinRequest::STATUS_ACCEPTED, JoinRequest::STATUS_DECLINED], true);

            JoinRequest::query()->create([
                'full_name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'phone' => fake()->optional(0.8)->phoneNumber(),
                'professional_type' => fake()->randomElement($roleTypes),
                'profession' => fake()->randomElement($professions),
                'about' => fake()->optional(0.95)->paragraphs(fake()->numberBetween(1, 2), true),
                'qualifications' => fake()->optional(0.8)->randomElements([
                    'BSc Nursing',
                    'First Aid Certified',
                    'Safeguarding Level 2',
                    'Medication Administration',
                    'Manual Handling',
                    'DBS Cleared',
                ], fake()->numberBetween(1, 3)),
                'resume_path' => fake()->optional(0.6)->randomElement([
                    'resumes/sample-cv-01.pdf',
                    'resumes/sample-cv-02.pdf',
                    'resumes/sample-cv-03.pdf',
                ]),
                'internal_notes' => fake()->optional(0.35)->sentence(),
                'status' => $status,
                'reviewed_by' => $isReviewed ? $reviewerId : null,
                'reviewed_at' => $isReviewed ? $createdAt->copy()->addDays(fake()->numberBetween(1, 5)) : null,
                'decided_at' => $isDecided ? $createdAt->copy()->addDays(fake()->numberBetween(2, 7)) : null,
                'created_at' => $createdAt,
                'updated_at' => now(),
            ]);
        }
    }

    protected function pickStatus(int $index): string
    {
        return match (true) {
            $index <= 16 => JoinRequest::STATUS_NEW,
            $index <= 26 => fake()->randomElement([JoinRequest::STATUS_ACCEPTED, JoinRequest::STATUS_DECLINED]),
            default => fake()->randomElement([JoinRequest::STATUS_NEW, JoinRequest::STATUS_ACCEPTED, JoinRequest::STATUS_DECLINED]),
        };
    }
}
