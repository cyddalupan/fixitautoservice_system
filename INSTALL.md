# Fixit Auto Services Management System - Installation Guide

## 📋 Overview

Fixit Auto Services is a comprehensive Laravel 11 application for managing automotive service operations. This guide covers complete installation, configuration, and deployment.

## 🚀 Quick Start

### Prerequisites
- **PHP 8.1+** with extensions: mbstring, xml, ctype, json, openssl, pdo_mysql, gd, zip
- **MySQL 5.7+** or MariaDB 10.3+
- **Composer** (PHP dependency manager)
- **Node.js 16+** and NPM
- **Web Server** (Apache/Nginx)
- **Git** (for version control)

### 1. Clone Repository
```bash
git clone git@github.com:cyddalupan/fixitautoservice_system.git
cd fixitautoservice_system
```

### 2. Install Dependencies
```bash
composer install --no-dev --optimize-autoloader
npm install --production
npm run build
```

### 3. Configure Environment
```bash
cp .env.example .env
# Edit .env with your database credentials and settings
nano .env
```

### 4. Generate Application Key
```bash
php artisan key:generate
```

### 5. Set Permissions
```bash
chown -R www-data:www-data storage
chown -R www-data:www-data bootstrap/cache
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### 6. Run Setup Commands
```bash
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 7. Access Application
- Visit: `http://your-domain.com`
- Default admin credentials: Check database seeders

## 📦 Complete Installation (Fresh Server)

### Step 1: Server Preparation

#### Ubuntu/Debian:
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP and extensions
sudo apt install -y php8.1 php8.1-cli php8.1-common php8.1-mysql \
    php8.1-zip php8.1-gd php8.1-mbstring php8.1-curl php8.1-xml \
    php8.1-bcmath php8.1-fpm php8.1-intl

# Install MySQL
sudo apt install -y mysql-server

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_16.x | sudo -E bash -
sudo apt install -y nodejs

# Install Git
sudo apt install -y git
```

#### CentOS/RHEL:
```bash
# Enable EPEL and REMI repositories
sudo dnf install -y epel-release
sudo dnf install -y https://rpms.remirepo.net/enterprise/remi-release-8.rpm

# Install PHP 8.1
sudo dnf module enable -y php:remi-8.1
sudo dnf install -y php php-cli php-fpm php-mysqlnd \
    php-zip php-gd php-mbstring php-curl php-xml \
    php-bcmath php-intl

# Install MySQL/MariaDB
sudo dnf install -y mariadb-server mariadb

# Install Composer, Node.js, Git
sudo dnf install -y composer nodejs git
```

### Step 2: Database Setup

```bash
# Login to MySQL
sudo mysql -u root -p

# Create database and user
CREATE DATABASE fixit_auto_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'fixit_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON fixit_auto_app.* TO 'fixit_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Step 3: Web Server Configuration

#### Apache:
```apache
<VirtualHost *:80>
    ServerName your-domain.com
    ServerAdmin webmaster@localhost
    DocumentRoot /var/www/fixit-auto-app/public

    <Directory /var/www/fixit-auto-app/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

#### Nginx:
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/fixit-auto-app/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### Step 4: SSL Configuration (HTTPS)

```bash
# Install Certbot (Let's Encrypt)
sudo apt install -y certbot python3-certbot-apache
# or for Nginx
sudo apt install -y certbot python3-certbot-nginx

# Obtain SSL certificate
sudo certbot --apache -d your-domain.com
# or for Nginx
sudo certbot --nginx -d your-domain.com
```

## 🔄 Migration from Existing Server

### Option A: Using Backup/Restore Scripts

#### 1. On Source Server:
```bash
cd /var/www/fixit-auto-app
./backup-fixit.sh
# Backup created in /tmp/fixit-backup-YYYYMMDD-HHMMSS/
```

#### 2. Transfer Backup:
```bash
# Method 1: SCP
scp -r /tmp/fixit-backup-* user@new-server:/tmp/

# Method 2: Rsync
rsync -avz /tmp/fixit-backup-* user@new-server:/tmp/

# Method 3: Download/Upload
tar -czf fixit-migration.tar.gz -C /tmp/fixit-backup-* .
# Download and upload to new server
```

#### 3. On Destination Server:
```bash
cd /var/www/fixit-auto-app
./restore-fixit.sh /tmp/fixit-backup-YYYYMMDD-HHMMSS
```

### Option B: Manual Migration

#### 1. Database Export/Import:
```bash
# Export from source
mysqldump -u root -p fixit_auto_app > fixit_auto_app.sql

# Import to destination
mysql -u root -p fixit_auto_app < fixit_auto_app.sql
```

#### 2. File Transfer:
```bash
# Exclude large directories
rsync -avz --exclude=vendor --exclude=node_modules \
    --exclude=storage/framework/cache --exclude=storage/logs \
    user@source-server:/var/www/fixit-auto-app/ /var/www/fixit-auto-app/
```

#### 3. Install Dependencies:
```bash
cd /var/www/fixit-auto-app
composer install --no-dev --optimize-autoloader
npm install --production
npm run build
```

## 🛠️ Configuration

### Environment Variables (.env)

```env
APP_NAME="Fixit Auto Services"
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=fixit_auto_app
DB_USERNAME=fixit_user
DB_PASSWORD=your_password

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@your-domain.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### Important Configuration Files

1. **`config/app.php`** - Application settings
2. **`config/database.php`** - Database configuration
3. **`config/mail.php`** - Email settings
4. **`config/filesystems.php`** - File storage
5. **`config/auth.php`** - Authentication settings

## 📊 Database Structure

### Key Tables:
- `users` - System users and authentication
- `customers` - Customer information
- `vehicles` - Vehicle details
- `appointments` - Service appointments
- `work_orders` - Repair work orders
- `inventory` - Parts inventory
- `invoices` - Billing and invoices
- `payments` - Payment records

### Database Seeders (Initial Data):
```bash
# Run database seeders
php artisan db:seed

# Specific seeders
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=CustomerSeeder
php artisan db:seed --class=VehicleSeeder
```

## 🔧 Maintenance

### Regular Tasks:

#### 1. Backup Database (Daily):
```bash
cd /var/www/fixit-auto-app
./backup-fixit.sh
# Consider adding to cron:
# 0 2 * * * /var/www/fixit-auto-app/backup-fixit.sh
```

#### 2. Clear Cache (As Needed):
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

#### 3. Update Dependencies:
```bash
composer update --no-dev
npm update
npm run build
```

#### 4. Check Logs:
```bash
tail -f storage/logs/laravel.log
```

### Cron Jobs (Scheduled Tasks):
```bash
# Edit crontab
crontab -e

# Add these lines:
* * * * * cd /var/www/fixit-auto-app && php artisan schedule:run >> /dev/null 2>&1
0 0 * * * cd /var/www/fixit-auto-app && php artisan backup:run
```

## 🐛 Troubleshooting

### Common Issues:

#### 1. "Permission Denied" Errors:
```bash
sudo chown -R www-data:www-data /var/www/fixit-auto-app
sudo chmod -R 775 /var/www/fixit-auto-app/storage
sudo chmod -R 775 /var/www/fixit-auto-app/bootstrap/cache
```

#### 2. Database Connection Errors:
- Verify `.env` database credentials
- Check MySQL service is running: `sudo systemctl status mysql`
- Test connection: `mysql -u username -p database_name`

#### 3. 500 Internal Server Error:
```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Enable debug mode temporarily
# In .env: APP_DEBUG=true
# Remember to disable in production!
```

#### 4. CSS/JS Not Loading:
```bash
# Rebuild assets
npm run build

# Clear cache
php artisan config:cache
php artisan view:cache
```

#### 5. Email Not Sending:
- Verify SMTP credentials in `.env`
- Check spam folder
- Test with: `php artisan tinker` then `Mail::raw('Test', fn($m) => $m->to('test@example.com')->subject('Test'))`

## 🔐 Security

### Essential Security Measures:

1. **Keep Software Updated:**
   ```bash
   sudo apt update && sudo apt upgrade
   composer update --no-dev
   ```

2. **Configure Firewall:**
   ```bash
   sudo ufw allow 80/tcp
   sudo ufw allow 443/tcp
   sudo ufw allow 22/tcp
   sudo ufw enable
   ```

3. **Secure Database:**
   ```bash
   sudo mysql_secure_installation
   # Remove anonymous users, disable remote root login
   ```

4. **File Permissions:**
   ```bash
   # Sensitive files
   chmod 600 .env
   chmod 600 storage/oauth-*.json
   
   # Directories
   find storage -type f -exec chmod 664 {} \;
   find storage -type d -exec chmod 775 {} \;
   ```

5. **Regular Backups:**
   ```bash
   # Automated backup script included
   ./backup-fixit.sh
   ```

## 📈 Monitoring

### Log Monitoring:
```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Web server logs
tail -f /var/log/apache2/error.log
tail -f /var/log/nginx/error.log

# System logs
tail -f /var/log/syslog
```

### Performance Monitoring:
```bash
# Check PHP-FPM status
sudo systemctl status php8.1-fpm

# Check MySQL status
sudo systemctl status mysql

# Check disk space
df -h

# Check memory usage
free -h
```

## 🤝 Support

### Getting Help:
1. **Check Documentation:** Review this INSTALL.md file
2. **Check Logs:** `storage/logs/laravel.log`
3. **GitHub Issues:** Report bugs at repository issues page
4. **Community:** Laravel community forums and Discord

### Emergency Recovery:
1. **Restore from Backup:** Use `restore-fixit.sh` script
2. **Rollback Code:** `git checkout previous-commit`
3. **Database Recovery:** Use MySQL binary logs if enabled

## 📝 Version History

- **v1.0.0** (Feb 23, 2026): Initial release with 15 core features
- **v1.1.0** (Mar 27, 2026): Bug fixes and enhancements
- **Current:** Production-ready automotive service management system

---

**Need Help?** Contact system administrator or refer to the source code documentation in `/docs` directory.

**Remember:** Always test changes in a staging environment before deploying to production!