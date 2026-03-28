<?php

return [
    'name' => 'Freelance Proposal Optimizer',
    'url' => Core\Env::get('APP_URL', 'http://freelance.local'),
    'debug' => Core\Env::get('APP_DEBUG', 'false') === 'true',
    'timezone' => 'America/New_York',
    'per_page' => 20,
    'upload_max_size' => 10 * 1024 * 1024, // 10MB
    'allowed_file_types' => ['pdf', 'doc', 'docx', 'txt', 'jpg', 'png'],
];
