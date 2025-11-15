<?php

declare(strict_types=1);

return [
    'host' => getenv('APP_DB_HOST') ?: 'localhost',
    'port' => (int)(getenv('APP_DB_PORT') ?: 3306),
    'database' => getenv('APP_DB_NAME') ?: 'tigerspe_youssef',
    'username' => getenv('APP_DB_USER') ?: 'tigerspe_youssef',
    'password' => getenv('APP_DB_PASS') ?: 'Klash18722@',
    'charset' => 'utf8mb4',
];
