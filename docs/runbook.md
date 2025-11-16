## تشغيل القاعدة والبوت – Runbook

### 1. تهيئة إعدادات البيئة
أنشئ ملف `.env` (أو استخدم متغيرات البيئة في الخادم) لضبط المفاتيح الحساسة:
```
APP_DB_PATH=/path/to/unified-bot/storage/database.sqlite

APP_TELEGRAM_TOKEN=123456:ABCDEF
SPIDER_BASE_URL=https://api.spider-service.com
SPIDER_API_KEY=live_spider_key
```

### 2. إنشاء قاعدة البيانات
```bash
mkdir -p storage
touch storage/database.sqlite
sqlite3 storage/database.sqlite < setup/schema.sql
sqlite3 storage/database.sqlite < setup/seed_settings.sql
```
تأكد من تعديل معرّفات القنوات في `seed_settings.sql` قبل التنفيذ.

### 3. نشر الكود وضبط الويب هوك
```bash
export APP_DB_PATH=/path/to/unified-bot/storage/database.sqlite
php -S 0.0.0.0:8080 -t /path/to/unified-bot
curl "https://api.telegram.org/bot$APP_TELEGRAM_TOKEN/setWebhook?url=https://yourdomain.com/index.php"
```

### 4. اختبار شراء رقم
1. افتح محادثة مع البوت واضغط `/start`.
2. اختر "قسم شراء الأرقام" → "شراء بالدولار".
3. اختر دولة ثم اضغط "تأكيد الشراء".
4. راقب:
   - خصم الرصيد من جدول `wallets`.
   - سجل العملية داخل `orders_numbers` و `transactions`.
   - وصول رسالة في قناة المبيعات (`notifications.sales_channel_id`).

### 5. اختبار طلب الكود
1. بعد الشراء، اضغط زر "طلب الكود".
2. تحقق من:
   - تحديث حالة الطلب إلى `delivered`.
   - ظهور رسالة نجاح للمستخدم تشمل الكود وكلمة المرور.
   - إشعار قناة النجاح (`notifications.success_channel_id`).
   - في حال فشل المزود، يتم إنشاء تذكرة جديدة في جداول `tickets` و`ticket_messages`.

### 6. الاشتراك الإجباري
قبل كل تفاعل، البوت يتحقق من عضوية المستخدم في القنوات المحددة. لتحديث القنوات:
```sql
UPDATE settings
SET value = json_set(
    value,
    '$.channels',
    json('[{"id": -100..., "link": "https://t.me/..."}]')
)
WHERE "key" = 'forced_subscription';
```

### 7. مراقبة السجلات
- أخطاء تيليجرام: `logs/telegram.log`
- يمكن إضافة tail للويب هوك لتتبع الطلبات.

### 8. التحقق من سلامة SQLite
يوجد سكربت تدقيق سريع للتأكد من أن الجداول الأساسية (users, wallets, transactions, orders_numbers, orders_smm, referrals, tickets, ticket_messages, star_payments, settings) متاحة وأن `PRAGMA integrity_check` تمر دون أخطاء:
```bash
php scripts/sqlite_audit.php
```
سيطبع السكربت عدد السجلات لكل جدول وأي ملاحظة تتعلق بسلامة القاعدة، ويمكن ربطه مع مهام مراقبة أو تشغيله بعد كل ترحيل.

### 9. النسخ الاحتياطي الدوري
للاستفادة من المجلد `storage/backups` يوجد سكربت ينسخ ملف SQLite (ويضغطه تلقائياً إلى GZip إذا توفّر `gzencode`):
```bash
php scripts/backup_database.php
```
أضفه إلى `cron` مثلاً:
```
0 * * * * cd /path/to/unified-bot && /usr/bin/php scripts/backup_database.php >/dev/null 2>&1
```
سيتم إنشاء ملف باسم `unified-bot-YYYYmmdd_HHMMSS.sqlite` (أو `.sqlite.gz`) داخل `storage/backups`.

### 10. تعريب أسماء الدول
يعتمد البوت الآن على الحقل `name_translations` داخل جدول `number_countries` لعرض اسم الدولة بنفس لغة المستخدم. قم بتعبئة الحقل بصيغة JSON تحتوي على اختصارات اللغات المتاحة (مثل `ar`, `en`, `ru`, `fa`, `cht`, `chb`, `tr`). مثال سريع:
```sql
UPDATE number_countries
SET name_translations = json_object(
    'ar', 'الولايات المتحدة',
    'en', 'United States',
    'ru', 'США'
)
WHERE code = 'US';
```
عند عدم توافر ترجمة بلغة المستخدم سيظهر الاسم الافتراضي (الإنجليزي)، لكن يوصى بتعبئة جميع الاختصارات لضمان تجربة متسقة.

باتباع الخطوات أعلاه يمكنك تشغيل البوت فعلياً والتحقق من مسارات شراء الرقم وطلب الكود والإشعارات، إضافةً إلى مراقبة سلامة SQLite والاحتفاظ بنسخ احتياطية حديثة. أي تغييرات إضافية (مثل دمج خدمات الرشق) ستتطلب تحديثات مشابهة في القاعدة والإعدادات.
