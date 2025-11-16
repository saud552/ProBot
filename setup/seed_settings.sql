-- Seed forced subscription & notification settings for SQLite
PRAGMA foreign_keys = ON;

BEGIN TRANSACTION;

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
