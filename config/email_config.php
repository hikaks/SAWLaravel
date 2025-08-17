<?php

return [
    'gmail' => [
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'username' => 'akkmalsyaiful@gmail.com',
        'password' => 'your-app-password', // Ganti dengan App Password dari Google
        'encryption' => 'tls',
        'from_address' => 'akkmalsyaiful@gmail.com',
        'from_name' => 'SAW Employee Evaluation System'
    ],
    
    'instructions' => [
        'step1' => 'Buka Google Account Settings',
        'step2' => 'Aktifkan 2-Factor Authentication',
        'step3' => 'Generate App Password untuk "Mail"',
        'step4' => 'Copy App Password dan paste di atas',
        'step5' => 'Restart Laravel server'
    ]
];

