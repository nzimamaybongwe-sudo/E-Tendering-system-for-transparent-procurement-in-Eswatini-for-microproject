<?php
return [
    'site_name' => 'MicroProjects Eswatini - E-Tendering System',
    'base_url' => '/E-Tendering system project/public',
    'bid_encryption_key' => getenv('BID_ENCRYPTION_KEY') ?: 'fallback_dev_key',
    'roles' => [
        'admin',
        'evaluator',
        'contractor',
        'officer'
    ],
    'role_registration_keys' => [
        'admin' => 'ADMIN2026!#',
        'evaluator' => 'EVALUATOR2026!#',
        'officer' => 'OFFICER2026!#'
    ]
];
