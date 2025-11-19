#!/bin/bash
# Script لمراقبة سجلات البوت

echo "=== مراقبة سجلات البوت ==="
echo "تاريخ البدء: $(date)"
echo ""

# مراقبة سجل الخادم
if [ -f logs/server.log ]; then
    echo "--- آخر 20 سطر من server.log ---"
    tail -20 logs/server.log
    echo ""
fi

# مراقبة سجل تيليجرام
if [ -f logs/telegram.log ]; then
    echo "--- آخر 20 سطر من telegram.log ---"
    tail -20 logs/telegram.log
    echo ""
else
    echo "ملف telegram.log غير موجود بعد"
    echo ""
fi

# التحقق من حالة الخادم
if pgrep -f "php -S 0.0.0.0:8080" > /dev/null; then
    echo "✓ الخادم يعمل على المنفذ 8080"
else
    echo "✗ الخادم غير يعمل"
fi

echo ""
echo "للمراقبة المستمرة، استخدم: tail -f logs/server.log logs/telegram.log"
