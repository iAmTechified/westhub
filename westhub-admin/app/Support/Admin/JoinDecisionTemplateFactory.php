<?php

namespace App\Support\Admin;

class JoinDecisionTemplateFactory
{
    public static function make(string $templateKey, string $fullName): array
    {
        return match ($templateKey) {
            'join.accepted' => [
                'subject' => 'WestHub Healthcare Application Update',
                'body' => "Dear {$fullName},\n\nCongratulations. Your application has been accepted.\n\nWhat happens next:\n- A coordinator will contact you to confirm onboarding documents.\n- You will receive your induction and role brief.\n- We will share your first scheduling window.\n\nWe are excited to welcome you to WestHub Healthcare.",
            ],
            'join.declined' => [
                'subject' => 'WestHub Healthcare Application Status',
                'body' => "Dear {$fullName},\n\nThank you for your interest in joining WestHub Healthcare.\n\nAfter careful review, we are unable to move forward with your application at this time. This does not reduce the value of your experience.\n\nWe appreciate the time you invested and wish you continued success.",
            ],
            default => [
                'subject' => 'WestHub Healthcare Notification',
                'body' => "Dear {$fullName},\n\nThere is an update regarding your application.",
            ],
        };
    }
}
