-- Seed forced subscription & notification settings for SQLite
PRAGMA foreign_keys = ON;

BEGIN TRANSACTION;

-- Insert default number provider
INSERT OR IGNORE INTO number_providers (id, name, base_url, api_key, status)
VALUES (1, 'Spider Service', 'https://api.spider-service.com', '5qu6cfg785yxf88g6tgr', 'active');

INSERT INTO settings ("key", "value")
VALUES
(
    'forced_subscription',
    json('{
        "enabled": true,
        "fallback_link": "https://t.me/K55DD",
        "channels": [
            {
                "id": -1002096907442,
                "link": "https://t.me/K55DD"
            }
        ]
    }')
),
(
    'notifications',
    json('{
        "sales_channel_id": -1003313387611,
        "success_channel_id": -1003397685474,
        "support_channel_id": -1002991395093
    }')
),
(
    'stars',
    json('{
        "usd_per_star": 0.011,
        "enabled": true
    }')
),
(
    'admins',
    json('{
        "ids": [985612253]
    }')
),
(
    'referrals',
    json('{
        "enabled": true,
        "bot_username": "SP1BOT",
        "reward_flat_usd": 0.5,
        "reward_percent": 0,
        "min_order_usd": 1,
        "max_per_user": 500
    }')
),
(
    'features',
    json('{
        "numbers_enabled": true,
        "smm_enabled": true,
        "support_enabled": true,
        "referrals_enabled": true,
        "stars_enabled": true
    }')
)
ON CONFLICT("key") DO UPDATE SET
    "value" = excluded."value",
    updated_at = CURRENT_TIMESTAMP;

COMMIT;
