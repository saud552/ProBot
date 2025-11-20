# 🚀 دليل البدء السريع
# Quick Start Guide

## للتشغيل السريع:

```bash
./start.sh
```

**هذا كل شيء!** السكريبت سيقوم بكل شيء تلقائياً.

---

## 📋 الخطوات:

1. **تشغيل السكريبت:**
   ```bash
   ./start.sh
   ```

2. **اختيار طريقة التشغيل:**
   - للاختبار المحلي: اختر `1`
   - للاستضافة بدون SSL: اختر `2` (Cloudflare Tunnel)
   - لإعداد Webhook: اختر `4`

3. **جاهز!** 🎉

---

## 🌐 للاستضافة:

```bash
# 1. ارفع الملفات
# 2. عبر SSH:
bash start.sh

# 3. اختر الخيار 4
# 4. أدخل: https://your-domain.com/index.php
```

---

## ⚡ أوامر سريعة:

```bash
# تشغيل مع Webhook مباشرة
./start.sh https://your-domain.com/index.php

# مراقبة السجلات
tail -f logs/server.log logs/telegram.log

# نسخ احتياطي
php scripts/backup_database.php
```

---

## ❓ مشاكل شائعة:

**خطأ: "Permission denied"**
```bash
chmod +x start.sh
```

**خطأ: "PHP not found"**
```bash
# Ubuntu/Debian
sudo apt-get install php php-cli

# CentOS
sudo yum install php php-cli
```

**خطأ: "Extensions missing"**
```bash
# Ubuntu/Debian
sudo apt-get install php-sqlite3 php-curl php-mbstring
```

---

## 📞 للمساعدة:

راجع `README_START.md` للدليل الكامل.
