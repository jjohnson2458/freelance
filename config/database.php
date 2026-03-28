<?php

return [
    'host' => Core\Env::get('DB_HOST', 'localhost'),
    'port' => Core\Env::get('DB_PORT', '3306'),
    'name' => Core\Env::get('DB_NAME', 'freelance'),
    'user' => Core\Env::get('DB_USER', 'root'),
    'password' => Core\Env::get('DB_PASSWORD', ''),
];
