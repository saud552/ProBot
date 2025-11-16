<?php

declare(strict_types=1);

return [
    'driver' => 'sqlite',
    'path' => getenv('APP_DB_PATH') ?: APP_BASE_PATH . '/storage/database.sqlite',
    'foreign_keys' => getenv('APP_DB_FOREIGN_KEYS') !== '0',
    'busy_timeout' => (int)(getenv('APP_DB_BUSY_TIMEOUT') ?: 5000),
    'journal_mode' => getenv('APP_DB_JOURNAL_MODE') ?: 'WAL',
];
