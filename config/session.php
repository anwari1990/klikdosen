<?php
return [
    'name' => 'MILDSESSID',
    'default' => 'file',
    'drivers' => [
        'cache' => [
            'prefix' => 'session',
            'expired' => 0
        ],
        'cookie' => [
            'prefix' => 'session',
            'expired' => 0,
            'path' => '/',
            'domain' => null,
            'secure' => false,
            'sameSite' => null,
            'httpOnly' => true
        ],
        'file' => [
            'prefix' => 'session',
            'path' => path('storage/sessions')
        ],
        'database' => [
            'prefix' => 'session',
            'table' => 'sessions',
            'columns' => [
                'id' => 'id',
                'payload' => 'payload',
                'last_activity' => 'last_activity'
            ]
        ]
    ]
];
