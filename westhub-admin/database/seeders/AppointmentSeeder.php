<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\County;
use App\Models\Service;
use App\Models\Township;
use App\Models\User;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = User::query()->pluck('id')->all();
        $countyIds = County::query()->pluck('id')->all();
        $townshipIds = Township::query()->pluck('id')->all();
        $serviceIds = Service::query()->pluck('id')->all();

        if (Appointment::query()->exists()) {
            return;
        }

        for ($i = 1; $i <= 48; $i++) {
            $status = $this->pickStatus($i);
            $createdAt = now()->subDays(fake()->numberBetween(0, 75))->subHours(fake()->numberBetween(0, 23));

            $scheduledAt = null;
            if (in_array($status, [
                Appointment::STATUS_CONFIRMED,
                Appointment::STATUS_RESCHEDULED,
                Appointment::STATUS_COMPLETED,
                Appointment::STATUS_CANCELLED,
            ], true)) {
                $scheduledAt = $createdAt->copy()
                    ->addDays(fake()->numberBetween(1, 14))
                    ->setTime(fake()->numberBetween(8, 17), fake()->randomElement([0, 30]));
            }

            $resolvedAt = in_array($status, [Appointment::STATUS_COMPLETED, Appointment::STATUS_CANCELLED], true)
                ? ($scheduledAt ? $scheduledAt->copy()->addHours(fake()->numberBetween(1, 24)) : $createdAt->copy()->addDays(fake()->numberBetween(2, 7)))
                : null;
            $preferredDate = fake()->boolean(85)
                ? fake()->dateTimeBetween($createdAt, $createdAt->copy()->addDays(21))->format('Y-m-d')
                : null;

            Appointment::query()->create([
                'full_name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'phone' => fake()->optional(0.85)->phoneNumber(),
                'county_id' => fake()->optional(0.75)->randomElement($countyIds ?: [null]),
                'township_id' => fake()->optional(0.65)->randomElement($townshipIds ?: [null]),
                'service_id' => fake()->optional(0.8)->randomElement($serviceIds ?: [null]),
                'preferred_date' => $preferredDate,
                'preferred_time' => fake()->optional(0.7)->randomElement(['Morning', 'Afternoon', 'Evening', '09:00', '10:30', '14:00', '16:30']),
                'message' => fake()->optional(0.6)->sentence(fake()->numberBetween(8, 20)),
                'source' => fake()->randomElement(['website', 'phone', 'referral']),
                'status' => $status,
                'scheduled_at' => $scheduledAt,
                'assigned_to' => (! empty($userIds) && in_array($status, [Appointment::STATUS_CONFIRMED, Appointment::STATUS_RESCHEDULED, Appointment::STATUS_COMPLETED], true))
                    ? fake()->randomElement($userIds)
                    : null,
                'resolved_at' => $resolvedAt,
                'meta' => [
                    'seeded' => true,
                    'priority' => fake()->randomElement(['normal', 'high']),
                ],
                'created_at' => $createdAt,
                'updated_at' => now(),
            ]);
        }
    }

    protected function pickStatus(int $index): string
    {
        $pool = match (true) {
            $index <= 14 => [Appointment::STATUS_NEW, Appointment::STATUS_CONFIRMED],
            $index <= 32 => [Appointment::STATUS_NEW, Appointment::STATUS_CONFIRMED, Appointment::STATUS_RESCHEDULED],
            default => [Appointment::STATUS_CONFIRMED, Appointment::STATUS_RESCHEDULED, Appointment::STATUS_COMPLETED, Appointment::STATUS_CANCELLED],
        };

        return fake()->randomElement($pool);
    }
}
