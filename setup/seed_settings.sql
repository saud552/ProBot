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
),
(
    'stars',
    JSON_OBJECT(
        'usd_per_star', 0.011,
        'enabled', TRUE
    )
),
(
    'admins',
    JSON_OBJECT(
        'ids', JSON_ARRAY(985612253)
    )
),
(
    'referrals',
    JSON_OBJECT(
        'enabled', TRUE,
        'bot_username', 'SP1BOT',
        'reward_flat_usd', 0.5,
        'reward_percent', 0,
        'min_order_usd', 1,
        'max_per_user', 500
    )
),
(
    'features',
    JSON_OBJECT(
        'numbers_enabled', TRUE,
        'smm_enabled', TRUE,
        'support_enabled', TRUE,
        'referrals_enabled', TRUE,
        'stars_enabled', TRUE
    )
)
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), updated_at = CURRENT_TIMESTAMP;
