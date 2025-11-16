## تشغيل القاعدة والبوت – Runbook

### 1. تهيئة إعدادات البيئة
أنشئ ملف `.env` (أو استخدم متغيرات البيئة في الخادم) لضبط المفاتيح الحساسة:
```
APP_DB_HOST=127.0.0.1
APP_DB_PORT=3306
APP_DB_NAME=unified_bot
APP_DB_USER=bot_user
APP_DB_PASS=bot_password

APP_TELEGRAM_TOKEN=123456:ABCDEF
SPIDER_BASE_URL=https://api.spider-service.com
SPIDER_API_KEY=live_spider_key
```

### 2. إنشاء قاعدة البيانات
```bash
mysql -u root -p -e "CREATE DATABASE unified_bot CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p unified_bot < setup/schema.sql
mysql -u root -p unified_bot < setup/seed_settings.sql
```
تأكد من تعديل معرّفات القنوات في `seed_settings.sql` قبل التنفيذ.

### 3. نشر الكود وضبط الويب هوك
```bash
export APP_DB_HOST=... # وغيرها كما في الخطوة 1
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
SET value = JSON_SET(value, '$.channels', JSON_ARRAY(JSON_OBJECT('id', -100..., 'link', 'https://t.me/...')))
WHERE `key` = 'forced_subscription';
```

### 7. مراقبة السجلات
- أخطاء تيليجرام: `logs/telegram.log`
- يمكن إضافة tail للويب هوك لتتبع الطلبات.

### 8. التحقق من تزامن MySQL
يوجد سكريبت تدقيق سريع للتأكد من أن الجداول الأساسية (users, wallets, transactions, orders_numbers, orders_smm, referrals, tickets, ticket_messages, star_payments, settings) متاحة وتستخدم فعلياً:
```bash
php scripts/mysql_audit.php
```
سيطبع السكريبت عدد السجلات وأي مشكلة اتصال، ويمكن ربطه مع مهام مراقبة أو تشغيله بعد كل ترحيل.

### 9. النسخ الاحتياطي الدوري
للاستفادة من المجلد `storage/backups` يوجد سكريبت نسخ احتياطي يولّد ملف SQL (ويضغطه تلقائياً إلى GZip إذا توفّر `gzencode`):
```bash
php scripts/backup_database.php
```
أضفه إلى `cron` مثلاً:
```
0 * * * * cd /path/to/unified-bot && /usr/bin/php scripts/backup_database.php >/dev/null 2>&1
```
سيتم إنشاء ملف باسم `unified-bot-YYYYmmdd_HHMMSS.sql.gz` داخل `storage/backups`.

### 10. تعريب أسماء الدول
يعتمد البوت الآن على الحقل `name_translations` داخل جدول `number_countries` لعرض اسم الدولة بنفس لغة المستخدم. قم بتعبئة الحقل بصيغة JSON تحتوي على اختصارات اللغات المتاحة (مثل `ar`, `en`, `ru`, `fa`, `cht`, `chb`, `tr`). مثال سريع:
```sql
UPDATE number_countries
SET name_translations = JSON_OBJECT(
    'ar', 'الولايات المتحدة',
    'en', 'United States',
    'ru', 'США'
)
WHERE code = 'US';
```
عند عدم توافر ترجمة بلغة المستخدم سيظهر الاسم الافتراضي (الإنجليزي)، لكن يوصى بتعبئة جميع الاختصارات لضمان تجربة متسقة.

باتباع الخطوات أعلاه يمكنك تشغيل البوت فعلياً والتحقق من مسارات شراء الرقم وطلب الكود والإشعارات، إضافةً إلى مراقبة التزامن مع MySQL والاحتفاظ بنسخ احتياطية حديثة. أي تغييرات إضافية (مثل دمج خدمات الرشق) ستتطلب تحديثات مشابهة في القاعدة والإعدادات.
