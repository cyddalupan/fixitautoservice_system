#!/bin/bash

# ============================================
# FIXIT AUTO SERVICES - RESTORE SCRIPT
# ============================================
# This script restores a Fixit Auto Services
# Laravel application from backup
# ============================================

set -e  # Exit on error

# Configuration
BACKUP_DIR="$1"
APP_DIR="/var/www/fixit-auto-app"
MYSQL_USER="root"
MYSQL_PASS=""  # Leave empty for system authentication or use -p option
DB_NAME="fixit_auto_app"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}============================================${NC}"
echo -e "${GREEN}  FIXIT AUTO SERVICES - RESTORE SCRIPT${NC}"
echo -e "${GREEN}============================================${NC}"

# Check if backup directory is provided
if [ -z "$BACKUP_DIR" ]; then
    echo -e "${RED}Usage: $0 <backup-directory>${NC}"
    echo "Example: $0 /tmp/fixit-backup-20240328-123456"
    exit 1
fi

# Check if backup directory exists
if [ ! -d "$BACKUP_DIR" ]; then
    echo -e "${RED}Backup directory not found: $BACKUP_DIR${NC}"
    exit 1
fi

echo -e "${YELLOW}Backup Source:${NC} $BACKUP_DIR"
echo -e "${YELLOW}Target Application:${NC} $APP_DIR"
echo ""

# Verify backup files exist
REQUIRED_FILES=("fixit_auto_app.sql" "fixit-app-code.tar.gz" ".env.backup")
for file in "${REQUIRED_FILES[@]}"; do
    if [ ! -f "$BACKUP_DIR/$file" ]; then
        echo -e "${RED}Missing required backup file: $file${NC}"
        exit 1
    fi
done
echo -e "${GREEN}✓ All required backup files found${NC}"
echo ""

# Create application directory if it doesn't exist
echo -e "${YELLOW}[1/7] Preparing application directory...${NC}"
if [ ! -d "$APP_DIR" ]; then
    mkdir -p "$APP_DIR"
    echo -e "${GREEN}  Created directory: $APP_DIR${NC}"
else
    echo -e "${YELLOW}  Directory exists: $APP_DIR${NC}"
    read -p "  ⚠️  Directory exists. Continue? (y/n): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo -e "${RED}Restoration cancelled${NC}"
        exit 0
    fi
fi

# Extract application code
echo -e "${YELLOW}[2/7] Extracting application code...${NC}"
tar -xzf "$BACKUP_DIR/fixit-app-code.tar.gz" -C "$APP_DIR"
echo -e "${GREEN}  ✓ Application code extracted${NC}"

# Create database if it doesn't exist
echo -e "${YELLOW}[3/7] Preparing database...${NC}"
if mysql -u "$MYSQL_USER" ${MYSQL_PASS:+-p$MYSQL_PASS} -e "USE $DB_NAME" 2>/dev/null; then
    echo -e "${YELLOW}  Database '$DB_NAME' exists${NC}"
    read -p "  ⚠️  Database exists. Drop and recreate? (y/n): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        mysql -u "$MYSQL_USER" ${MYSQL_PASS:+-p$MYSQL_PASS} -e "DROP DATABASE IF EXISTS $DB_NAME;"
        mysql -u "$MYSQL_USER" ${MYSQL_PASS:+-p$MYSQL_PASS} -e "CREATE DATABASE $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
        echo -e "${GREEN}  ✓ Database recreated${NC}"
    else
        echo -e "${YELLOW}  ⚠️  Using existing database${NC}"
    fi
else
    mysql -u "$MYSQL_USER" ${MYSQL_PASS:+-p$MYSQL_PASS} -e "CREATE DATABASE $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    echo -e "${GREEN}  ✓ Database created${NC}"
fi

# Restore database
echo -e "${YELLOW}[4/7] Restoring database...${NC}"
mysql -u "$MYSQL_USER" ${MYSQL_PASS:+-p$MYSQL_PASS} "$DB_NAME" < "$BACKUP_DIR/fixit_auto_app.sql"
echo -e "${GREEN}  ✓ Database restored${NC}"

# Restore .env file
echo -e "${YELLOW}[5/7] Restoring configuration...${NC}"
cp "$BACKUP_DIR/.env.backup" "$APP_DIR/.env"
echo -e "${GREEN}  ✓ .env file restored${NC}"
echo -e "${YELLOW}  ⚠️  IMPORTANT: Edit $APP_DIR/.env with new server details${NC}"
echo "    - Update DB_HOST, DB_USERNAME, DB_PASSWORD"
echo "    - Update APP_URL, MAIL settings, API keys"

# Restore uploaded files if they exist
echo -e "${YELLOW}[6/7] Restoring uploaded files...${NC}"
if [ -f "$BACKUP_DIR/fixit-uploads.tar.gz" ]; then
    mkdir -p "$APP_DIR/storage/app/public"
    tar -xzf "$BACKUP_DIR/fixit-uploads.tar.gz" -C "$APP_DIR/storage/app/public/"
    echo -e "${GREEN}  ✓ Uploaded files restored${NC}"
else
    echo -e "${YELLOW}  ⚠️  No uploaded files backup found${NC}"
fi

# Set permissions
echo -e "${YELLOW}[7/7] Setting permissions...${NC}"
chown -R www-data:www-data "$APP_DIR/storage"
chown -R www-data:www-data "$APP_DIR/bootstrap/cache"
chmod -R 775 "$APP_DIR/storage"
chmod -R 775 "$APP_DIR/bootstrap/cache"
echo -e "${GREEN}  ✓ Permissions set${NC}"

# Installation instructions
echo -e "${GREEN}============================================${NC}"
echo -e "${GREEN}  RESTORATION COMPLETE!${NC}"
echo -e "${GREEN}============================================${NC}"
echo ""
echo -e "${YELLOW}Next Steps:${NC}"
echo ""
echo "1. Edit environment configuration:"
echo "   nano $APP_DIR/.env"
echo ""
echo "2. Install dependencies:"
echo "   cd $APP_DIR"
echo "   composer install --no-dev --optimize-autoloader"
echo "   npm install --production"
echo "   npm run build"
echo ""
echo "3. Generate application key:"
echo "   php artisan key:generate"
echo ""
echo "4. Run Laravel setup commands:"
echo "   php artisan storage:link"
echo "   php artisan config:cache"
echo "   php artisan route:cache"
echo "   php artisan view:cache"
echo ""
echo "5. Test the application:"
echo "   php artisan serve &"
echo "   curl http://localhost:8000"
echo ""
echo "6. Set up web server (Apache/Nginx):"
echo "   - Point document root to: $APP_DIR/public"
echo "   - Configure .htaccess or server blocks"
echo ""
echo -e "${GREEN}============================================${NC}"