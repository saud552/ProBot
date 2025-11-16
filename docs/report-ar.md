## تقرير شامل عن البوت الموحد

### مقدمة
تم إنشاء هذا البوت لتجميع كل وظائف بوت الأرقام وبوت الرشق داخل مشروع واحد يعتمد على معمارية طبقية واضحة وقاعدة بيانات MySQL. يشرح هذا التقرير الملفات الأساسية، متطلبات التشغيل، وهيكل المجلدات، بالإضافة إلى كيفية النشر والتشغيل العملي.

### المتطلبات العامة للتشغيل
- **قاعدة البيانات**: MySQL 8 (أو متوافق) مع مستخدم يمتلك صلاحيات `CREATE/ALTER/INSERT/UPDATE`.
- **بيئة PHP**: إصدار 8.1 أو أعلى مع امتدادات `curl`, `json`, `pdo_mysql`.
- **مفاتيح حساسة (Environment Variables)**: يتم ضبطها إما عبر متغيرات البيئة أو عن طريق أي أداة إدارة أسرار:
  - `APP_DB_HOST`, `APP_DB_PORT`, `APP_DB_NAME`, `APP_DB_USER`, `APP_DB_PASS`.
  - `APP_TELEGRAM_TOKEN` : توكن بوت تيليجرام.
  - `SPIDER_BASE_URL`, `SPIDER_API_KEY`: مزود الأرقام (Spider Service).
  - `ORBITEXA_BASE_URL`, `ORBITEXA_API_KEY`: مزود الرشق Orbitexa.
  - قنوات الإشعارات ومعرّفات الإدمن يتم تفعيلها من خلال جدول `settings` (انظر `setup/seed_settings.sql` أو استخدم لوحة التحكم).
- **أذونات الكتابة**: المجلدات `logs/`, `storage/`, `storage/backups/` يجب أن تُمنح صلاحيات الكتابة لحساب التشغيل.

### هيكل المجلدات والملفات (مع وصف)
```
unified-bot/
├── bootstrap.php              # تهيئة autoloader وإنشاء مجلدي logs/backups
├── index.php                  # نقطة الدخول الأساسية (Webhook) وتجميع التبعيات
├── config/
│   ├── database.php           # قراءة إعدادات MySQL من البيئة
│   ├── providers.php          # إعداد مفاتيح مزودي Spider وOrbitexa
│   └── telegram.php           # إعدادات تيليجرام (توكن، اسم المستخدم، الوقت المستغرق)
├── docs/
│   ├── architecture.md        # توثيق المعمارية الطبقية وأسباب إعادة البناء
│   ├── data-model.md          # شرح مخطط قاعدة البيانات (بما في ذلك name_translations)
│   ├── runbook.md             # دليل التشغيل، التهيئة، النسخ الاحتياطي، وملاحظات الترجمة
│   └── report-ar.md           # هذا التقرير
├── lang/
│   └── translations.php       # ترجمات الواجهات لكل اللغات المدعومة (ar, en, ru, fa, cht, chb, tr)
├── logs/                      # يتم إنشاء سجلات التتبع والأخطاء هنا
├── scripts/
│   ├── backup_database.php    # سكربت نسخ احتياطي SQL (gzip اختياري) يحفظ في storage/backups
│   └── mysql_audit.php        # فحص سريع للتأكد من وجود الجداول الأساسية وعدد سجلاتها
├── setup/
│   ├── schema.sql             # إنشاء كامل للجداول (users, wallets, orders, star_payments...)
│   └── seed_settings.sql      # بيانات افتراضية (إدمن، قنوات، إعدادات Stars, Referrals)
├── src/
│   ├── Domain/                # طبقة المجال (المستخدمين، الأعداد، SMM، التذاكر، الدفع بالنجوم)
│   ├── Infrastructure/        # الوصول إلى قاعدة البيانات، المزودين الخارجيين، Telegram client
│   └── Presentation/          # BotKernel (منطق التفاعل) + لوحات المفاتيح
├── storage/
│   ├── backups/               # نواتج سكربت النسخ الاحتياطي
│   ├── langs.json             # ذاكرة مؤقتة لاختيار اللغة لكل مستخدم
│   ├── smm_flow.json          # حالات محادثة الرشق
│   ├── support_flow.json      # حالات محادثة التذاكر
│   └── admin_flow.json        # حالات إدخال لوحة التحكم
└── logs/telegram.log (ينشأ تلقائياً عند الحاجة)
```

### ملاحظات حول الترجمة وأسماء الدول
- يتضمن جدول `number_countries` عمودًا جديدًا `name_translations` بصيغة JSON لتخزين الاسم باللغات المدعومة.
- يقوم `BotKernel` بتمرير رمز اللغة الحالي (`_lang`) إلى `NumberCatalogService` بحيث يرى كل مستخدم أسماء الدول بلغته.
- عند غياب ترجمة معينة، يتم الرجوع إلى الاسم الافتراضي (الإنجليزي).

### ضبط الإعدادات الحساسة
1. **القيم الفورية**: يمكنك تعديل `setup/seed_settings.sql` قبل الاستيراد الأولي لإضافة:
   - `notifications` (قنوات المبيعات/النجاح/الدعم).
   - `forced_subscription` (القنوات الإجبارية).
   - `admins.ids` (معرّفات تيليجرام للإدارة).
   - `stars.usd_per_star` و`stars.enabled`.
   - `referrals` (نسبة المكافأة، الحد الأدنى للطلب...).
2. **بعد التشغيل**: يمكن للإدمن ضبط هذه القيم مباشرة من داخل لوحة التحكم (`Admin Panel`) دون تعديل قاعدة البيانات يدويًا.

### خطوات النشر والتشغيل
1. **إعداد قاعدة البيانات**:
   ```bash
   mysql -u root -p -e "CREATE DATABASE unified_bot CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   mysql -u root -p unified_bot < setup/schema.sql
   mysql -u root -p unified_bot < setup/seed_settings.sql
   ```
2. **ضبط المتغيرات البيئية** (مثال Bash):
   ```bash
   export APP_DB_HOST=127.0.0.1
   export APP_DB_PORT=3306
   export APP_DB_NAME=unified_bot
   export APP_DB_USER=bot_user
   export APP_DB_PASS=super_secret
   export APP_TELEGRAM_TOKEN=123456:ABCDEF
   export SPIDER_BASE_URL=https://api.spider-service.com
   export SPIDER_API_KEY=live_spider_key
   export ORBITEXA_BASE_URL=https://orbitexa.com/api/v2
   export ORBITEXA_API_KEY=orbitexa_key
   ```
3. **تشغيل خادم PHP للاختبار المحلي**:
   ```bash
   php -S 0.0.0.0:8080 -t /path/to/unified-bot
   ```
4. **تعيين Webhook لدى تيليجرام**:
   ```bash
   curl "https://api.telegram.org/bot$APP_TELEGRAM_TOKEN/setWebhook?url=https://yourdomain.com/index.php"
   ```
5. **فحص صحة الاتصال**:
   ```bash
   php scripts/mysql_audit.php
   ```
6. **النسخ الاحتياطي الدوري** (مثال cron كل ساعة):
   ```
   0 * * * * cd /path/to/unified-bot && /usr/bin/php scripts/backup_database.php >/dev/null 2>&1
   ```

### ملاحظات إضافية للتشغيل
- تأكد من أن خدمة الويب (Apache/Nginx) تسمح بتمرير طلبات POST إلى `index.php`.
- يمكن للإدمنين تحديث أسعار النجوم/البلدان أو تعطيل الأقسام من داخل لوحة التحكم.
- سجلات الأخطاء تُكتب افتراضيًا إلى `logs/telegram.log` ويمكن مراقبتها عبر `tail -f`.

### خاتمة
يعتمد البوت الموحد على معمارية واضحة تمكن من الصيانة وإضافة الميزات بسهولة، مع دعم كامل لتعدد اللغات وطرق الدفع (الدولار/النجوم) وإدارة شاملة عبر لوحة إدمن واحدة. باتباع هذا التقرير يمكن لأي مشغل إعداد البيئة وتشغيلها بثقة.
