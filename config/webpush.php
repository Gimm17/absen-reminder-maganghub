<?php

return [
    'vapid' => [
        'subject'    => env('VAPID_SUBJECT', 'mailto:admin@localhost'),
        'public_key' => env('VAPID_PUBLIC_KEY'),
        'private_key'=> env('VAPID_PRIVATE_KEY'),
    ],
];