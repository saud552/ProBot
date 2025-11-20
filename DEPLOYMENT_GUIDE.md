# دليل نشر البوت على الاستضافة
# Bot Deployment Guide

## المتطلبات الأساسية

### 1. متطلبات الخادم (Server Requirements)
- PHP 8.1 أو أحدث (يفضل PHP 8.3)
- SQLite3 extension
- cURL extension
- JSON extension
- mbstring extension
- صلاحيات كتابة على المجلدات: `storage/`, `logs/`, `tmp/`

### 2. متطلبات الويب
- خادم ويب يدعم PHP (Apache, Nginx, أو PHP Built-in Server)
- SSL Certificate (HTTPS) - **مطلوب لـ Telegram Webhook**

---

## خطوات النشر

### الطريقة 1: استخدام Apache/Nginx مع PHP-FPM

#### 1. رفع الملفات
```bash
# رفع جميع الملفات إلى الاستضافة عبر FTP/SFTP
# تأكد من رفع:
# - جميع ملفات src/
# - config/
# - lang/
# - setup/
# - index.php
# - bootstrap.php
```

#### 2. إعداد قاعدة البيانات
```bash
# على الخادم، قم بتشغيل:
cd /path/to/your/bot
php -r "require 'bootstrap.php'; require 'setup/schema.sql';"
# أو استورد schema.sql يدوياً
```

#### 3. إعداد الصلاحيات
```bash
chmod 755 storage/
chmod 755 logs/
chmod 755 tmp/
chmod 644 storage/database.sqlite
chmod 644 storage/*.json
```

#### 4. إعداد Apache Virtual Host
```apache
<VirtualHost *:443>
    ServerName your-domain.com
    DocumentRoot /path/to/your/bot
    
    SSLEngine on
    SSLCertificateFile /path/to/cert.pem
    SSLCertificateKeyFile /path/to/key.pem
    
    <Directory /path/to/your/bot>
        AllowOverride All
        Require all granted
    </Directory>
    
    # توجيه جميع الطلبات إلى index.php
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php [QSA,L]
</VirtualHost>
```

#### 5. إعداد Nginx
```nginx
server {
    listen 443 ssl http2;
    server_name your-domain.com;
    root /path/to/your/bot;
    index index.php;

    ssl_certificate /path/to/cert.pem;
    ssl_certificate_key /path/to/key.pem;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

---

### الطريقة 2: استخدام PHP Built-in Server (للتطوير/اختبار)

```bash
# على الخادم
cd /path/to/your/bot
php -S 0.0.0.0:8080 index.php
```

**ملاحظة:** هذه الطريقة غير مناسبة للإنتاج. استخدم Apache/Nginx.

---

### الطريقة 3: استخدام Supervisor (للإنتاج)

#### 1. تثبيت Supervisor
```bash
sudo apt-get install supervisor
```

#### 2. إنشاء ملف إعداد Supervisor
```bash
sudo nano /etc/supervisor/conf.d/telegram-bot.conf
```

#### 3. محتوى الملف:
```ini
[program:telegram-bot]
command=php -S 0.0.0.0:8080 index.php
directory=/path/to/your/bot
user=www-data
autostart=true
autorestart=true
redirect_stderr=true
stdout_logfile=/path/to/your/bot/logs/supervisor.log
```

#### 4. تشغيل Supervisor
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start telegram-bot
```

---

## إعداد Webhook

### 1. الحصول على URL آمن (HTTPS)
- يجب أن يكون لديك نطاق مع SSL
- أو استخدم خدمة مثل Cloudflare Tunnel

### 2. إعداد Webhook
```bash
# على الخادم
cd /path/to/your/bot
php setup_webhook.php https://your-domain.com/index.php
```

أو يدوياً:
```bash
curl "https://api.telegram.org/botYOUR_BOT_TOKEN/setWebhook?url=https://your-domain.com/index.php"
```

---

## إعداد المتغيرات البيئية (اختياري)

### إنشاء ملف `.env`:
```bash
APP_TELEGRAM_TOKEN=your_bot_token
SPIDER_API_KEY=your_api_key
SPIDER_BASE_URL=https://api.spider-service.com
```

### تعديل `config/telegram.php` و `config/providers.php` لقراءة من `.env`

---

## التحقق من النشر

### 1. التحقق من Webhook
```bash
curl "https://api.telegram.org/botYOUR_BOT_TOKEN/getWebhookInfo"
```

### 2. التحقق من السجلات
```bash
tail -f /path/to/your/bot/logs/server.log
tail -f /path/to/your/bot/logs/telegram.log
```

### 3. اختبار البوت
- أرسل `/start` للبوت
- تحقق من السجلات

---

## نصائح الأمان

1. **حماية الملفات الحساسة:**
   ```bash
   chmod 600 config/telegram.php
   chmod 600 config/providers.php
   ```

2. **حماية مجلد storage:**
   ```apache
   # في .htaccess
   <Files "database.sqlite">
       Require all denied
   </Files>
   ```

3. **إخفاء معلومات PHP:**
   ```php
   // في php.ini
   expose_php = Off
   display_errors = Off
   ```

---

## استكشاف الأخطاء

### البوت لا يستجيب
1. تحقق من السجلات: `logs/server.log`
2. تحقق من Webhook: `getWebhookInfo`
3. تحقق من SSL: يجب أن يكون HTTPS
4. تحقق من الصلاحيات على المجلدات

### خطأ في قاعدة البيانات
1. تحقق من صلاحيات `storage/database.sqlite`
2. تحقق من وجود `number_providers` في قاعدة البيانات
3. قم بتشغيل `setup/schema.sql` مرة أخرى

### خطأ 500 Internal Server Error
1. تحقق من `logs/server.log`
2. تحقق من `error_log` في PHP
3. تأكد من تثبيت جميع Extensions المطلوبة

---

## مثال كامل للنشر على استضافة مشتركة

### 1. رفع الملفات عبر FTP
```
/public_html/
  ├── index.php
  ├── bootstrap.php
  ├── config/
  ├── src/
  ├── lang/
  ├── setup/
  ├── storage/
  └── logs/
```

### 2. إنشاء `.htaccess` في المجلد الرئيسي
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# حماية الملفات الحساسة
<FilesMatch "\.(sqlite|json|log)$">
    Require all denied
</FilesMatch>
```

### 3. إعداد Webhook
```bash
php setup_webhook.php https://your-domain.com/index.php
```

---

## استخدام Cloudflare Tunnel (بدون SSL خاص)

إذا لم يكن لديك SSL، يمكن استخدام Cloudflare Tunnel:

```bash
# تثبيت cloudflared
wget https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64
chmod +x cloudflared-linux-amd64
sudo mv cloudflared-linux-amd64 /usr/local/bin/cloudflared

# تشغيل tunnel
cloudflared tunnel --url http://localhost:8080

# سيتم إعطاؤك URL مثل:
# https://random-name.trycloudflare.com
# استخدمه في webhook
```

---

## الصيانة الدورية

### 1. نسخ احتياطي لقاعدة البيانات
```bash
# يمكن استخدام scripts/backup_database.php
php scripts/backup_database.php
```

### 2. تنظيف السجلات
```bash
# حذف السجلات القديمة (أكثر من 30 يوم)
find logs/ -name "*.log" -mtime +30 -delete
```

---

## الدعم

في حالة وجود مشاكل:
1. تحقق من السجلات
2. تحقق من صلاحيات الملفات
3. تحقق من إعدادات PHP
4. تحقق من Webhook URL
