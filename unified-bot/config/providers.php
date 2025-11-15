<?php

declare(strict_types=1);

return [
    'numbers' => [
        'spider' => [
            'base_url' => getenv('SPIDER_BASE_URL') ?: 'https://api.spider-service.com',
            'api_key' => getenv('SPIDER_API_KEY') ?: '5qu6cfg785yxf88g6tgr',
        ],
    ],
];
