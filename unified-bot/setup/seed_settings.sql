-- Seed forced subscription & notification settings
INSERT INTO settings (`key`, `value`) VALUES
(
    'forced_subscription',
    JSON_OBJECT(
        'enabled', TRUE,
        'fallback_link', 'https://t.me/YourMainChannel',
        'channels', JSON_ARRAY(
            JSON_OBJECT(
                'id', -1001234567890,
                'link', 'https://t.me/YourMainChannel'
            )
        )
    )
),
(
    'notifications',
    JSON_OBJECT(
        'sales_channel_id', -1002222222222,
        'success_channel_id', -1003333333333,
        'support_channel_id', -1004444444444
    )
)
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), updated_at = CURRENT_TIMESTAMP;
