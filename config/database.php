<?php

declare(strict_types=1);

return [
    'host' => getenv('APP_DB_HOST') ?: '127.0.0.1',
    'port' => (int)(getenv('APP_DB_PORT') ?: 3306),
    'database' => getenv('APP_DB_NAME') ?: 'bot_database',
    'username' => getenv('APP_DB_USER') ?: 'bot_user',
    'password' => getenv('APP_DB_PASS') ?: '',
    'charset' => getenv('APP_DB_CHARSET') ?: 'utf8mb4',
];
