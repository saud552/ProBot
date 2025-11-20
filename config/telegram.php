<?php

declare(strict_types=1);

return [
    'token' => getenv('APP_TELEGRAM_TOKEN') ?: '7174411191:AAHhRIakJPu0B_9bxMsIlkGfMvRNYsYge7A',
    'timeout' => (int)(getenv('APP_TELEGRAM_TIMEOUT') ?: 15),
    'connect_timeout' => (int)(getenv('APP_TELEGRAM_CONNECT_TIMEOUT') ?: 5),
    'bot_username' => getenv('APP_TELEGRAM_USERNAME') ?: 'SP1BOT',
];
