-- Seed forced subscription & notification settings
INSERT INTO settings (`key`, `value`) VALUES
(
    'forced_subscription',
    JSON_OBJECT(
        'enabled', TRUE,
        'fallback_link', 'https://t.me/K55DD',
        'channels', JSON_ARRAY(
            JSON_OBJECT(
                'id', -1002096907442,
                'link', 'https://t.me/K55DD'
            )
        )
    )
),
(
    'notifications',
    JSON_OBJECT(
        'sales_channel_id', -1003313387611,
        'success_channel_id', -1003397685474,
        'support_channel_id', -1002991395093
    )
)
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), updated_at = CURRENT_TIMESTAMP;
