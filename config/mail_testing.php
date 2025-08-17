<?php

return [
    'default' => 'log',

    'mailers' => [
        'log' => [
            'transport' => 'log',
            'channel' => 'mail',
        ],
    ],

    'from' => [
        'address' => 'noreply@saw-system.com',
        'name' => 'SAW Employee Evaluation System',
    ],
];

