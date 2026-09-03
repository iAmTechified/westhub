<?php

return [
    'contact' => [
        'email' => env('WESTHUB_CONTACT_EMAIL', env('MAIL_FROM_ADDRESS')),
        'phone' => env('WESTHUB_CONTACT_PHONE', '+1 2246250423'),
    ],
];
