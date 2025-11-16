## Unified Data Model (MySQL + JSON Fallback)

> يوضح هذا المستند مخطط البيانات الأساسي للبوت الموحد، ويكمل ما جاء في `architecture.md`. يعتمد النظام على MySQL كمصدر رئيسي مع إمكانية حفظ نسخ JSON احتياطية (مثل `storage/backups/*.json`) أو استخدام `JsonStore` لبيانات مؤقتة صغيرة.

### المبادئ
- **فصل الاهتمامات**: كل جدول مسؤول عن مفهوم واحد فقط (Users، Wallets، Orders، Services ...).
- **التناسق**: جميع العمليات الحرجة (خصم الرصيد، إنشاء طلب) تمر عبر معاملات MySQL.
- **إمكانية العودة**: أجزاء معينة (مثل إعدادات العرض أو نسخ الاحتياط للغات) يمكن تدوينها بصيغة JSON عند الضرورة.

### الجداول الأساسية
| الجدول | الوصف السريع | حقول مهمة |
|--------|--------------|-----------|
| `users` | يمثل مستخدم تيليجرام | `telegram_id (UNIQUE)`, `language_code`, `is_banned`, `created_at`, `updated_at` |
| `profiles` | تخزين معلومات تكميلية (الاسم، اسم المستخدم) | `user_id FK`, `first_name`, `username`, `referrer_id` |
| `wallets` | رصيد المستخدم لكل عملة | `user_id FK`, `currency`, `balance`, `updated_at` |
| `transactions` | سجل الحركات المالية | `user_id FK`, `type`, `method`, `amount`, `currency`, `meta`, `created_at` |
| `referrals` | تتبع الإحالات والمكافآت | `user_id FK`, `inviter_id`, `bonus_amount`, `created_at` |
| `settings` | إعدادات عامة (اشتراك إجباري، هامش، الخ) | `key`, `value JSON`, `updated_at` |
| `number_providers` | مزودو الأرقام (Spider Service، TG-Accounts) | `name`, `api_key`, `base_url`, `status` |
| `number_countries` | الدول المتاحة لشراء الأرقام | `code`, `name`, `name_translations JSON`, `price_usd`, `provider_id`, `is_active` |
| `orders_numbers` | طلبات شراء الأرقام | `id`, `user_id`, `country_code`, `price`, `currency`, `hash_code`, `status`, `metadata JSON`, `created_at` |
| `services` | خدمات الرشق (مستمدة من TigerSpeed) | `id`, `category_id`, `provider_code`, `name`, `rate_per_1k`, `min`, `max`, `currency`, `is_active` |
| `service_categories` | أقسام الخدمات | `id`, `code`, `name`, `caption`, `is_active` |
| `orders_smm` | طلبات الرشق | `id`, `user_id`, `service_id`, `link`, `quantity`, `price`, `currency`, `status`, `provider_order_id`, `created_at` |
| `tickets` | نظام التذاكر والدعم | `id`, `user_id`, `status`, `subject`, `last_message_at` |
| `ticket_messages` | رسائل التذاكر | `id`, `ticket_id`, `sender_type`, `message`, `created_at` |
| `action_locks` | قفل العمليات الحرجة | `user_id`, `action`, `expires_at` |

### JSON Fallback / Cache
- `storage/langs.json`: اللغة الحالية لكل مستخدم (للتحميل السريع، بينما يبقى الحقل الرسمي في جدول `users.language_code`).
- `storage/backups/*.json`: نسخ دورية من `orders_numbers`, `orders_smm`, `transactions` لتسهيل الاسترجاع اليدوي.
- `storage/settings.cache.json`: نسخة مخبئية من `settings` لسرعة القراءة (يتم تحديثها عند كل تعديل).

### العلاقات والعقود
- كل من `orders_numbers` و`orders_smm` يرتبطان بسجل في `transactions` لضمان التتبع المالي.
- قيم العملات في `wallets` و `transactions` تقاس بالدقة الرباعية (`DECIMAL(18,4)`).
- أي إعداد يمكن تخصيصه لكل مستخدم (مثل الاشتراك الإجباري) يدوّن في `settings` تحت مفتاح `forced_subscription` ويتاح عبر طبقة الخدمة.

### اعتبارات إضافية
- **المفاتيح الخارجية**: جميع العلاقات تحتوي على `ON DELETE CASCADE` حين يكون ذلك منطقيًا (مثل حذف user ➜ حذف tickets).
- **كثافة القراءة**: الجداول التي تتطلب قراءة عالية (مثل `number_countries`, `services`) يمكن مزامنتها إلى JSON cache أو Redis لاحقًا.
- **الترحيل**: سيتم بناء سكربت `setup/schema.sql` (انظر أدناه) لإنشاء القاعدة نظيفة، على أن يضاف سكربت ترحيل لاحق لقراءة بيانات Number/TigerSpeed ونقلها.
