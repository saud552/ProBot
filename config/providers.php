<?php

declare(strict_types=1);

return [
    'numbers' => [
        'spider' => [
            'base_url' => getenv('SPIDER_BASE_URL') ?: 'https://api.spider-service.com',
            'api_key' => getenv('SPIDER_API_KEY') ?: '',
        ],
    ],
    'smm' => [
        'orbitexa' => [
            'base_url' => getenv('ORBITEXA_BASE_URL') ?: 'https://orbitexa.com/api/v2',
            'api_key' => getenv('ORBITEXA_API_KEY') ?: '',
        ],
    ],
];
