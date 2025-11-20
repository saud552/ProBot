#!/bin/bash

# Script for deploying Telegram Bot to production server
# Usage: ./deploy.sh

set -e

echo "🚀 Starting deployment..."

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Check PHP version
PHP_VERSION=$(php -r "echo PHP_VERSION;")
echo -e "${YELLOW}PHP Version: $PHP_VERSION${NC}"

if ! php -r "exit(version_compare(PHP_VERSION, '8.1.0', '>=') ? 0 : 1);"; then
    echo -e "${RED}Error: PHP 8.1 or higher is required${NC}"
    exit 1
fi

# Check required extensions
echo "Checking PHP extensions..."
REQUIRED_EXTENSIONS=("pdo" "pdo_sqlite" "curl" "json" "mbstring")
for ext in "${REQUIRED_EXTENSIONS[@]}"; do
    if ! php -m | grep -q "$ext"; then
        echo -e "${RED}Error: PHP extension '$ext' is not installed${NC}"
        exit 1
    fi
done
echo -e "${GREEN}✓ All required extensions are installed${NC}"

# Create necessary directories
echo "Creating directories..."
mkdir -p storage/backups
mkdir -p logs
mkdir -p tmp
chmod 755 storage logs tmp
echo -e "${GREEN}✓ Directories created${NC}"

# Check database
if [ ! -f "storage/database.sqlite" ]; then
    echo "Database not found. Creating..."
    sqlite3 storage/database.sqlite < setup/schema.sql
    sqlite3 storage/database.sqlite < setup/seed_settings.sql
    chmod 644 storage/database.sqlite
    echo -e "${GREEN}✓ Database created${NC}"
else
    echo -e "${GREEN}✓ Database exists${NC}"
fi

# Check if number_providers table has data
PROVIDER_COUNT=$(sqlite3 storage/database.sqlite "SELECT COUNT(*) FROM number_providers;" 2>/dev/null || echo "0")
if [ "$PROVIDER_COUNT" -eq "0" ]; then
    echo "Adding default provider..."
    sqlite3 storage/database.sqlite "INSERT OR IGNORE INTO number_providers (id, name, base_url, api_key, status) VALUES (1, 'Spider Service', 'https://api.spider-service.com', '5qu6cfg785yxf88g6tgr', 'active');"
    echo -e "${GREEN}✓ Default provider added${NC}"
fi

# Test PHP syntax
echo "Testing PHP syntax..."
find . -name "*.php" -not -path "./tmp/*" -not -path "./vendor/*" -exec php -l {} \; > /dev/null
echo -e "${GREEN}✓ PHP syntax is valid${NC}"

# Check file permissions
echo "Checking file permissions..."
chmod 644 storage/*.json 2>/dev/null || true
chmod 644 storage/database.sqlite 2>/dev/null || true
echo -e "${GREEN}✓ File permissions set${NC}"

echo ""
echo -e "${GREEN}✅ Deployment completed successfully!${NC}"
echo ""
echo "Next steps:"
echo "1. Set up webhook: php setup_webhook.php https://your-domain.com/index.php"
echo "2. Check logs: tail -f logs/server.log"
echo "3. Test the bot by sending /start"
