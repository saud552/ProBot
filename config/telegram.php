<?php

declare(strict_types=1);

return [
    'token' => getenv('APP_TELEGRAM_TOKEN') ?: '',
    'timeout' => (int)(getenv('APP_TELEGRAM_TIMEOUT') ?: 15),
    'connect_timeout' => (int)(getenv('APP_TELEGRAM_CONNECT_TIMEOUT') ?: 5),
    'bot_username' => getenv('APP_TELEGRAM_USERNAME') ?: 'SP1BOT',
];
