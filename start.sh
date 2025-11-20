#!/bin/bash

###############################################################################
# Telegram Bot - Automated Setup and Start Script
# سكريبت شامل لتشغيل البوت تلقائياً
###############################################################################

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color
BOLD='\033[1m'

# Script directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

# Configuration
BOT_NAME="Telegram Bot"
PHP_MIN_VERSION="8.1"
REQUIRED_EXTENSIONS=("pdo" "pdo_sqlite" "curl" "json" "mbstring")

###############################################################################
# Helper Functions
###############################################################################

print_header() {
    echo ""
    echo -e "${BOLD}${CYAN}═══════════════════════════════════════════════════════════${NC}"
    echo -e "${BOLD}${CYAN}  $1${NC}"
    echo -e "${BOLD}${CYAN}═══════════════════════════════════════════════════════════${NC}"
    echo ""
}

print_success() {
    echo -e "${GREEN}✓${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

print_info() {
    echo -e "${BLUE}ℹ${NC} $1"
}

print_step() {
    echo -e "${CYAN}[$(date +%H:%M:%S)]${NC} $1"
}

###############################################################################
# Check System Requirements
###############################################################################

check_php() {
    print_step "التحقق من PHP..."
    
    if ! command -v php &> /dev/null; then
        print_error "PHP غير مثبت!"
        echo ""
        echo "قم بتثبيت PHP:"
        echo "  Ubuntu/Debian: sudo apt-get install php php-cli php-sqlite3 php-curl php-mbstring"
        echo "  CentOS/RHEL: sudo yum install php php-cli php-pdo php-sqlite php-curl php-mbstring"
        echo "  macOS: brew install php"
        exit 1
    fi
    
    PHP_VERSION=$(php -r "echo PHP_VERSION;")
    print_success "PHP مثبت: $PHP_VERSION"
    
    # Check version
    if ! php -r "exit(version_compare(PHP_VERSION, '$PHP_MIN_VERSION', '>=') ? 0 : 1);"; then
        print_error "يتطلب PHP $PHP_MIN_VERSION أو أحدث (المثبت: $PHP_VERSION)"
        exit 1
    fi
    
    print_success "إصدار PHP مناسب"
}

check_extensions() {
    print_step "التحقق من إضافات PHP..."
    
    MISSING_EXTENSIONS=()
    for ext in "${REQUIRED_EXTENSIONS[@]}"; do
        if ! php -m | grep -qi "^$ext$"; then
            MISSING_EXTENSIONS+=("$ext")
        fi
    done
    
    if [ ${#MISSING_EXTENSIONS[@]} -gt 0 ]; then
        print_error "إضافات PHP مفقودة: ${MISSING_EXTENSIONS[*]}"
        echo ""
        echo "قم بتثبيتها:"
        echo "  Ubuntu/Debian: sudo apt-get install php-sqlite3 php-curl php-mbstring"
        echo "  CentOS/RHEL: sudo yum install php-pdo php-sqlite php-curl php-mbstring"
        exit 1
    fi
    
    print_success "جميع الإضافات المطلوبة مثبتة"
}

check_sqlite() {
    print_step "التحقق من SQLite..."
    
    if ! command -v sqlite3 &> /dev/null; then
        print_warning "sqlite3 غير مثبت (اختياري، لكن يُنصح به)"
    else
        SQLITE_VERSION=$(sqlite3 --version | cut -d' ' -f1)
        print_success "SQLite مثبت: $SQLITE_VERSION"
    fi
}

###############################################################################
# Setup Directories and Permissions
###############################################################################

setup_directories() {
    print_step "إعداد المجلدات..."
    
    DIRS=("storage" "storage/backups" "logs" "tmp")
    for dir in "${DIRS[@]}"; do
        if [ ! -d "$dir" ]; then
            mkdir -p "$dir"
            print_success "تم إنشاء: $dir"
        else
            print_info "موجود: $dir"
        fi
    done
}

setup_permissions() {
    print_step "إعداد الصلاحيات..."
    
    # Directories
    chmod 755 storage logs tmp 2>/dev/null || true
    chmod 755 storage/backups 2>/dev/null || true
    
    # Database
    if [ -f "storage/database.sqlite" ]; then
        chmod 644 storage/database.sqlite
        print_success "تم تعيين صلاحيات قاعدة البيانات"
    fi
    
    # JSON files
    find storage -name "*.json" -type f -exec chmod 644 {} \; 2>/dev/null || true
    
    # Scripts
    chmod +x deploy.sh 2>/dev/null || true
    chmod +x monitor.sh 2>/dev/null || true
    
    print_success "تم تعيين الصلاحيات"
}

###############################################################################
# Database Setup
###############################################################################

setup_database() {
    print_step "إعداد قاعدة البيانات..."
    
    DB_FILE="storage/database.sqlite"
    
    if [ ! -f "$DB_FILE" ]; then
        print_info "إنشاء قاعدة بيانات جديدة..."
        
        # Create empty database
        touch "$DB_FILE"
        chmod 644 "$DB_FILE"
        
        # Run schema
        if [ -f "setup/schema.sql" ]; then
            if command -v sqlite3 &> /dev/null; then
                sqlite3 "$DB_FILE" < setup/schema.sql
                print_success "تم تنفيذ schema.sql"
            else
                # Use PHP to create database
                php -r "
                require 'bootstrap.php';
                use App\Infrastructure\Database\Connection;
                use App\Infrastructure\Database\SchemaManager;
                \$config = require 'config/database.php';
                \$conn = new Connection(\$config);
                SchemaManager::ensure(
                    \$conn->pdo(),
                    'setup/schema.sql',
                    'setup/seed_settings.sql'
                );
                echo 'Database created successfully\n';
                "
                print_success "تم إنشاء قاعدة البيانات عبر PHP"
            fi
        else
            print_error "ملف setup/schema.sql غير موجود!"
            exit 1
        fi
    else
        print_info "قاعدة البيانات موجودة"
    fi
    
    # Check if number_providers has data
    if command -v sqlite3 &> /dev/null; then
        PROVIDER_COUNT=$(sqlite3 "$DB_FILE" "SELECT COUNT(*) FROM number_providers;" 2>/dev/null || echo "0")
        if [ "$PROVIDER_COUNT" -eq "0" ]; then
            print_info "إضافة مزود افتراضي..."
            sqlite3 "$DB_FILE" "INSERT OR IGNORE INTO number_providers (id, name, base_url, api_key, status) VALUES (1, 'Spider Service', 'https://api.spider-service.com', '5qu6cfg785yxf88g6tgr', 'active');" 2>/dev/null || true
            print_success "تم إضافة المزود الافتراضي"
        fi
    fi
    
    print_success "قاعدة البيانات جاهزة"
}

###############################################################################
# Validate Configuration
###############################################################################

validate_config() {
    print_step "التحقق من ملفات الإعداد..."
    
    REQUIRED_FILES=(
        "config/telegram.php"
        "config/database.php"
        "config/providers.php"
        "bootstrap.php"
        "index.php"
    )
    
    for file in "${REQUIRED_FILES[@]}"; do
        if [ ! -f "$file" ]; then
            print_error "ملف مفقود: $file"
            exit 1
        fi
    done
    
    print_success "جميع ملفات الإعداد موجودة"
    
    # Check PHP syntax
    print_step "التحقق من صحة كود PHP..."
    PHP_ERRORS=0
    while IFS= read -r -d '' file; do
        if ! php -l "$file" > /dev/null 2>&1; then
            print_error "خطأ في: $file"
            PHP_ERRORS=$((PHP_ERRORS + 1))
        fi
    done < <(find . -name "*.php" -not -path "./tmp/*" -not -path "./vendor/*" -print0 2>/dev/null)
    
    if [ $PHP_ERRORS -eq 0 ]; then
        print_success "جميع ملفات PHP صحيحة"
    else
        print_warning "تم العثور على $PHP_ERRORS خطأ في ملفات PHP"
    fi
}

###############################################################################
# Webhook Setup
###############################################################################

setup_webhook() {
    print_step "إعداد Webhook..."
    
    # Check if webhook URL is provided as argument
    WEBHOOK_URL="$1"
    
    if [ -z "$WEBHOOK_URL" ]; then
        echo ""
        print_info "هل تريد إعداد Webhook الآن؟ (y/n)"
        read -r SETUP_WEBHOOK
        
        if [ "$SETUP_WEBHOOK" != "y" ] && [ "$SETUP_WEBHOOK" != "Y" ]; then
            print_info "سيتم تخطي إعداد Webhook"
            return
        fi
        
        echo ""
        print_info "أدخل Webhook URL (مثال: https://your-domain.com/index.php)"
        print_info "أو اضغط Enter للتخطي:"
        read -r WEBHOOK_URL
    fi
    
    if [ -n "$WEBHOOK_URL" ]; then
        if [ -f "setup_webhook.php" ]; then
            php setup_webhook.php "$WEBHOOK_URL"
            if [ $? -eq 0 ]; then
                print_success "تم إعداد Webhook بنجاح"
            else
                print_error "فشل إعداد Webhook"
            fi
        else
            print_warning "ملف setup_webhook.php غير موجود"
        fi
    else
        print_info "تم تخطي إعداد Webhook"
    fi
}

###############################################################################
# Start Options
###############################################################################

show_start_options() {
    echo ""
    print_header "خيارات التشغيل"
    echo ""
    echo "1) تشغيل البوت محلياً (PHP Built-in Server)"
    echo "2) تشغيل البوت مع Cloudflare Tunnel (للاستضافة بدون SSL)"
    echo "3) عرض معلومات Webhook الحالية"
    echo "4) إعادة إعداد Webhook"
    echo "5) مراقبة السجلات فقط"
    echo "6) الخروج"
    echo ""
    print_info "اختر خياراً (1-6):"
    read -r OPTION
    
    case $OPTION in
        1)
            start_local_server
            ;;
        2)
            start_with_cloudflare
            ;;
        3)
            check_webhook_info
            ;;
        4)
            setup_webhook_interactive
            ;;
        5)
            monitor_logs
            ;;
        6)
            print_success "وداعاً!"
            exit 0
            ;;
        *)
            print_error "خيار غير صحيح"
            show_start_options
            ;;
    esac
}

start_local_server() {
    print_header "تشغيل البوت محلياً"
    
    PORT="${PORT:-8080}"
    print_info "سيتم التشغيل على المنفذ: $PORT"
    print_info "اضغط Ctrl+C لإيقاف البوت"
    echo ""
    
    php -S 0.0.0.0:$PORT index.php
}

start_with_cloudflare() {
    print_header "تشغيل البوت مع Cloudflare Tunnel"
    
    # Check if cloudflared is installed
    if ! command -v cloudflared &> /dev/null; then
        print_warning "cloudflared غير مثبت"
        echo ""
        print_info "هل تريد تثبيته تلقائياً؟ (y/n)"
        read -r INSTALL_CLOUDFLARE
        
        if [ "$INSTALL_CLOUDFLARE" = "y" ] || [ "$INSTALL_CLOUDFLARE" = "Y" ]; then
            install_cloudflared
        else
            print_error "يجب تثبيت cloudflared أولاً"
            return
        fi
    fi
    
    PORT="${PORT:-8080}"
    print_info "بدء تشغيل الخادم المحلي على المنفذ $PORT..."
    
    # Start PHP server in background
    php -S 0.0.0.0:$PORT index.php > logs/server.log 2>&1 &
    PHP_PID=$!
    print_success "تم تشغيل الخادم (PID: $PHP_PID)"
    
    sleep 2
    
    # Start cloudflared
    print_info "بدء تشغيل Cloudflare Tunnel..."
    cloudflared tunnel --url http://localhost:$PORT > logs/cloudflared.log 2>&1 &
    CLOUDFLARE_PID=$!
    
    sleep 5
    
    # Extract URL
    if [ -f "logs/cloudflared.log" ]; then
        TUNNEL_URL=$(grep -oE "https://[a-z0-9-]+\.trycloudflare\.com" logs/cloudflared.log | head -1)
        if [ -n "$TUNNEL_URL" ]; then
            print_success "تم الحصول على URL: $TUNNEL_URL"
            echo ""
            print_info "هل تريد إعداد Webhook تلقائياً؟ (y/n)"
            read -r SETUP_WEBHOOK
            
            if [ "$SETUP_WEBHOOK" = "y" ] || [ "$SETUP_WEBHOOK" = "Y" ]; then
                php setup_webhook.php "$TUNNEL_URL/index.php"
            fi
            
            echo ""
            print_success "البوت يعمل الآن!"
            print_info "URL: $TUNNEL_URL/index.php"
            print_info "اضغط Ctrl+C لإيقاف البوت"
            echo ""
            
            # Wait for user interrupt
            trap "kill $PHP_PID $CLOUDFLARE_PID 2>/dev/null; exit" INT TERM
            wait
        else
            print_error "فشل الحصول على URL من cloudflared"
            kill $PHP_PID 2>/dev/null
        fi
    fi
}

install_cloudflared() {
    print_step "تثبيت cloudflared..."
    
    ARCH=$(uname -m)
    OS=$(uname -s | tr '[:upper:]' '[:lower:]')
    
    if [ "$OS" = "linux" ]; then
        if [ "$ARCH" = "x86_64" ]; then
            DOWNLOAD_URL="https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64"
        elif [ "$ARCH" = "aarch64" ] || [ "$ARCH" = "arm64" ]; then
            DOWNLOAD_URL="https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-arm64"
        else
            print_error "معمارية غير مدعومة: $ARCH"
            return 1
        fi
    else
        print_error "نظام تشغيل غير مدعوم: $OS"
        return 1
    fi
    
    print_info "تحميل cloudflared..."
    wget -q "$DOWNLOAD_URL" -O /tmp/cloudflared
    chmod +x /tmp/cloudflared
    
    # Try to install system-wide
    if [ -w "/usr/local/bin" ]; then
        sudo mv /tmp/cloudflared /usr/local/bin/cloudflared 2>/dev/null || mv /tmp/cloudflared ./cloudflared
        print_success "تم تثبيت cloudflared"
    else
        mv /tmp/cloudflared ./cloudflared
        print_success "تم تحميل cloudflared في المجلد الحالي"
        export PATH="$PATH:$SCRIPT_DIR"
    fi
}

check_webhook_info() {
    print_header "معلومات Webhook"
    
    TOKEN=$(php -r "require 'config/telegram.php'; echo \$config['token'];")
    
    if [ -z "$TOKEN" ]; then
        print_error "لم يتم العثور على توكن البوت"
        return
    fi
    
    print_info "جارٍ التحقق من Webhook..."
    RESPONSE=$(curl -s "https://api.telegram.org/bot$TOKEN/getWebhookInfo")
    
    echo ""
    echo "$RESPONSE" | python3 -m json.tool 2>/dev/null || echo "$RESPONSE"
    echo ""
}

setup_webhook_interactive() {
    print_header "إعداد Webhook"
    
    echo ""
    print_info "أدخل Webhook URL:"
    read -r WEBHOOK_URL
    
    if [ -n "$WEBHOOK_URL" ]; then
        php setup_webhook.php "$WEBHOOK_URL"
    else
        print_error "URL غير صحيح"
    fi
}

monitor_logs() {
    print_header "مراقبة السجلات"
    
    if [ ! -f "logs/server.log" ] && [ ! -f "logs/telegram.log" ]; then
        print_warning "لا توجد سجلات للعرض"
        return
    fi
    
    print_info "جارٍ عرض السجلات (اضغط Ctrl+C للخروج)..."
    echo ""
    
    tail -f logs/server.log logs/telegram.log 2>/dev/null || tail -f logs/server.log
}

###############################################################################
# Main Execution
###############################################################################

main() {
    clear
    print_header "إعداد وتشغيل $BOT_NAME"
    
    # System checks
    check_php
    check_extensions
    check_sqlite
    
    # Setup
    setup_directories
    setup_permissions
    setup_database
    validate_config
    
    # Summary
    echo ""
    print_header "ملخص الإعداد"
    print_success "جميع المتطلبات جاهزة!"
    echo ""
    print_info "المسار الحالي: $SCRIPT_DIR"
    print_info "قاعدة البيانات: storage/database.sqlite"
    print_info "السجلات: logs/"
    echo ""
    
    # Ask about webhook if URL provided as argument
    if [ -n "$1" ]; then
        setup_webhook "$1"
    fi
    
    # Show start options
    show_start_options
}

# Run main function
main "$@"
