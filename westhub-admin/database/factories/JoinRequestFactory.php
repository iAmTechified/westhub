<?php

namespace Database\Factories;

use App\Models\JoinRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JoinRequest>
 */
class JoinRequestFactory extends Factory
{
    protected $model = JoinRequest::class;

    public function definition(): array
    {
        $status = fake()->randomElement([
            JoinRequest::STATUS_NEW,
            JoinRequest::STATUS_ACCEPTED,
            JoinRequest::STATUS_DECLINED,
        ]);

        $createdAt = fake()->dateTimeBetween('-60 days', 'now');
        $isDecided = in_array($status, [JoinRequest::STATUS_ACCEPTED, JoinRequest::STATUS_DECLINED], true);

        return [
            'full_name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->optional(0.8)->phoneNumber(),
            'professional_type' => fake()->randomElement([JoinRequest::ROLE_SKILLED, JoinRequest::ROLE_NON_SKILLED]),
            'profession' => fake()->optional(0.9)->randomElement([
                'Nurse',
                'Care Assistant',
                'Support Worker',
                'Physiotherapist',
                'Healthcare Assistant',
            ]),
            'position_applied_for' => fake()->optional(0.8)->jobTitle(),
            'about' => fake()->optional(0.85)->paragraph(),
            'status' => $status,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
            'reviewed_at' => $isDecided ? fake()->dateTimeBetween($createdAt, 'now') : null,
            'decided_at' => $isDecided ? fake()->dateTimeBetween($createdAt, 'now') : null,
        ];
    }
}
