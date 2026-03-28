#!/bin/bash

# ============================================
# FIXIT AUTO SERVICES - BACKUP SCRIPT
# ============================================
# This script creates a complete backup of the
# Fixit Auto Services Laravel application
# ============================================

set -e  # Exit on error

# Configuration
BACKUP_DIR="/tmp/fixit-backup-$(date +%Y%m%d-%H%M%S)"
MYSQL_USER="root"
MYSQL_PASS=""  # Leave empty for system authentication or use -p option
APP_DIR="/var/www/fixit-auto-app"
DB_NAME="fixit_auto_app"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}============================================${NC}"
echo -e "${GREEN}  FIXIT AUTO SERVICES - BACKUP SCRIPT${NC}"
echo -e "${GREEN}============================================${NC}"

# Create backup directory
echo -e "${YELLOW}[1/6] Creating backup directory...${NC}"
mkdir -p "$BACKUP_DIR"
echo "Backup directory: $BACKUP_DIR"

# Backup database
echo -e "${YELLOW}[2/6] Backing up database '$DB_NAME'...${NC}"
if [ -z "$MYSQL_PASS" ]; then
    mysqldump -u "$MYSQL_USER" "$DB_NAME" > "$BACKUP_DIR/fixit_auto_app.sql"
else
    mysqldump -u "$MYSQL_USER" -p"$MYSQL_PASS" "$DB_NAME" > "$BACKUP_DIR/fixit_auto_app.sql"
fi

# Verify database backup
if [ -s "$BACKUP_DIR/fixit_auto_app.sql" ]; then
    DB_SIZE=$(du -h "$BACKUP_DIR/fixit_auto_app.sql" | cut -f1)
    echo -e "${GREEN}  ✓ Database backup created: $DB_SIZE${NC}"
else
    echo -e "${RED}  ✗ Database backup failed or is empty!${NC}"
    exit 1
fi

# Backup .env file (sensitive - handle with care!)
echo -e "${YELLOW}[3/6] Backing up configuration files...${NC}"
cp "$APP_DIR/.env" "$BACKUP_DIR/.env.backup"
echo -e "${GREEN}  ✓ .env file backed up${NC}"

# Backup application code (excluding large directories)
echo -e "${YELLOW}[4/6] Backing up application code...${NC}"
tar -czf "$BACKUP_DIR/fixit-app-code.tar.gz" \
    -C "$APP_DIR" \
    --exclude=vendor \
    --exclude=node_modules \
    --exclude=storage/framework/cache \
    --exclude=storage/logs \
    --exclude=storage/debugbar \
    --exclude=.git \
    .

APP_SIZE=$(du -h "$BACKUP_DIR/fixit-app-code.tar.gz" | cut -f1)
echo -e "${GREEN}  ✓ Application code backed up: $APP_SIZE${NC}"

# Backup uploaded files
echo -e "${YELLOW}[5/6] Backing up uploaded files...${NC}"
if [ -d "$APP_DIR/storage/app/public" ]; then
    tar -czf "$BACKUP_DIR/fixit-uploads.tar.gz" \
        -C "$APP_DIR/storage/app/public" .
    UPLOAD_SIZE=$(du -h "$BACKUP_DIR/fixit-uploads.tar.gz" | cut -f1)
    echo -e "${GREEN}  ✓ Uploaded files backed up: $UPLOAD_SIZE${NC}"
else
    echo -e "${YELLOW}  ⚠ No uploaded files directory found${NC}"
fi

# Create backup manifest
echo -e "${YELLOW}[6/6] Creating backup manifest...${NC}"
cat > "$BACKUP_DIR/BACKUP_MANIFEST.md" << EOF
# Fixit Auto Services Backup Manifest
Generated: $(date)

## Backup Contents
1. Database: fixit_auto_app.sql
   - Size: $(du -h "$BACKUP_DIR/fixit_auto_app.sql" | cut -f1)
   - Tables: $(grep -c "CREATE TABLE" "$BACKUP_DIR/fixit_auto_app.sql" 2>/dev/null || echo "Unknown")

2. Application Code: fixit-app-code.tar.gz
   - Size: $(du -h "$BACKUP_DIR/fixit-app-code.tar.gz" | cut -f1)
   - Source: $APP_DIR

3. Configuration: .env.backup
   - Contains: Database credentials, API keys, app settings

4. Uploaded Files: fixit-uploads.tar.gz
   - Size: $(du -h "$BACKUP_DIR/fixit-uploads.tar.gz" 2>/dev/null | cut -f1 || echo "N/A")
   - Source: $APP_DIR/storage/app/public

## Restoration Instructions
1. Extract application code:
   \`\`\`bash
   tar -xzf fixit-app-code.tar.gz -C /var/www/fixit-auto-app/
   \`\`\`

2. Restore database:
   \`\`\`bash
   mysql -u root -p fixit_auto_app < fixit_auto_app.sql
   \`\`\`

3. Restore uploaded files:
   \`\`\`bash
   tar -xzf fixit-uploads.tar.gz -C /var/www/fixit-auto-app/storage/app/public/
   \`\`\`

4. Restore .env file:
   \`\`\`bash
   cp .env.backup /var/www/fixit-auto-app/.env
   # Edit with new server details
   \`\`\`
EOF

echo -e "${GREEN}  ✓ Backup manifest created${NC}"

# Summary
echo -e "${GREEN}============================================${NC}"
echo -e "${GREEN}  BACKUP COMPLETE!${NC}"
echo -e "${GREEN}============================================${NC}"
echo ""
echo -e "${YELLOW}Backup Location:${NC} $BACKUP_DIR"
echo -e "${YELLOW}Contents:${NC}"
ls -lh "$BACKUP_DIR"
echo ""
echo -e "${YELLOW}Total Size:${NC} $(du -sh "$BACKUP_DIR" | cut -f1)"
echo ""
echo -e "${YELLOW}Next Steps:${NC}"
echo "1. Secure the backup directory (contains sensitive data)"
echo "2. Transfer to new server if migrating"
echo "3. Follow restoration instructions in BACKUP_MANIFEST.md"
echo ""
echo -e "${GREEN}============================================${NC}"