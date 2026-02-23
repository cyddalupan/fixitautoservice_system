# Deployment Guide

## Overview

This guide provides comprehensive instructions for deploying the Fixit Auto Services application to production environments. The application supports multiple deployment scenarios including traditional hosting, Docker containers, and cloud platforms.

## Table of Contents

1. [Prerequisites](#prerequisites)
2. [Environment Setup](#environment-setup)
3. [Traditional Deployment](#traditional-deployment)
4. [Docker Deployment](#docker-deployment)
5. [Cloud Deployment](#cloud-deployment)
6. [Database Setup](#database-setup)
7. [Application Configuration](#application-configuration)
8. [Security Configuration](#security-configuration)
9. [Performance Optimization](#performance-optimization)
10. [Monitoring & Maintenance](#monitoring--maintenance)
11. [Backup & Recovery](#backup--recovery)
12. [Troubleshooting](#troubleshooting)

## Prerequisites

### System Requirements

#### Minimum Requirements
- **CPU**: 2 cores
- **RAM**: 4GB
- **Storage**: 20GB SSD
- **OS**: Ubuntu 20.04 LTS or later, CentOS 8+, or compatible Linux distribution

#### Recommended Requirements
- **CPU**: 4+ cores
- **RAM**: 8GB+
- **Storage**: 50GB+ SSD with RAID configuration
- **OS**: Ubuntu 22.04 LTS

### Software Requirements

#### Required Software
- **PHP**: 8.2 or higher
- **MySQL**: 8.0 or higher (or MariaDB 10.5+)
- **Web Server**: Nginx 1.18+ or Apache 2.4+
- **Composer**: 2.5+
- **Node.js**: 18.x or 20.x
- **Redis**: 6.0+ (recommended for caching)

#### Optional Software
- **Supervisor**: For queue workers
- **Certbot**: For SSL certificates
- **Fail2ban**: For security
- **Monitoring**: Prometheus, Grafana

### Network Requirements
- **Ports**: 80 (HTTP), 443 (HTTPS), 3306 (MySQL), 6379 (Redis)
- **DNS**: Domain name with A/AAAA records
- **SSL**: SSL certificate (Let's Encrypt recommended)
- **Firewall**: Properly configured firewall rules

## Environment Setup

### Server Preparation

1. **Update System**
   ```bash
   sudo apt update
   sudo apt upgrade -y
   ```

2. **Install Required Packages**
   ```bash
   sudo apt install -y software-properties-common curl git unzip
   ```

3. **Create Application User**
   ```bash
   sudo adduser --system --group --home /var/www/fixit-auto-app fixit
   ```

### PHP Installation

1. **Add PHP Repository**
   ```bash
   sudo add-apt-repository ppa:ondrej/php -y
   sudo apt update
   ```

2. **Install PHP and Extensions**
   ```bash
   sudo apt install -y php8.2 php8.2-fpm php8.2-mysql php8.2-mbstring \
     php8.2-xml php8.2-bcmath php8.2-curl php8.2-zip php8.2-gd \
     php8.2-redis php8.2-intl
   ```

3. **Verify PHP Installation**
   ```bash
   php --version
   php -m | grep -E "mysql|mbstring|xml|bcmath|curl|zip|gd|redis|intl"
   ```

### Database Installation

1. **Install MySQL**
   ```bash
   sudo apt install -y mysql-server
   ```

2. **Secure MySQL Installation**
   ```bash
   sudo mysql_secure_installation
   ```

3. **Create Application Database**
   ```mysql
   CREATE DATABASE fixit_auto CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'fixit_user'@'localhost' IDENTIFIED BY 'strong_password_here';
   GRANT ALL PRIVILEGES ON fixit_auto.* TO 'fixit_user'@'localhost';
   FLUSH PRIVILEGES;
   ```

### Web Server Installation

#### Option A: Nginx (Recommended)

1. **Install Nginx**
   ```bash
   sudo apt install -y nginx
   ```

2. **Configure PHP-FPM**
   ```bash
   sudo nano /etc/php/8.2/fpm/pool.d/www.conf
   ```
   Update:
   ```ini
   user = fixit
   group = fixit
   listen.owner = fixit
   listen.group = fixit
   ```

3. **Restart PHP-FPM**
   ```bash
   sudo systemctl restart php8.2-fpm
   ```

#### Option B: Apache

1. **Install Apache**
   ```bash
   sudo apt install -y apache2 libapache2-mod-php8.2
   ```

2. **Enable Required Modules**
   ```bash
   sudo a2enmod rewrite
   sudo a2enmod headers
   sudo a2enmod expires
   ```

### Redis Installation (Optional but Recommended)

1. **Install Redis**
   ```bash
   sudo apt install -y redis-server
   ```

2. **Configure Redis**
   ```bash
   sudo nano /etc/redis/redis.conf
   ```
   Update:
   ```conf
   maxmemory 256mb
   maxmemory-policy allkeys-lru
   ```

3. **Start and Enable Redis**
   ```bash
   sudo systemctl enable redis-server
   sudo systemctl start redis-server
   ```

## Traditional Deployment

### Application Deployment

1. **Clone Repository**
   ```bash
   cd /var/www
   sudo git clone https://github.com/your-org/fixit-auto-app.git
   sudo chown -R fixit:fixit fixit-auto-app
   ```

2. **Install Dependencies**
   ```bash
   cd fixit-auto-app
   sudo -u fixit composer install --no-dev --optimize-autoloader
   sudo -u fixit npm install
   sudo -u fixit npm run build
   ```

3. **Configure Environment**
   ```bash
   cp .env.example .env
   nano .env
   ```

4. **Generate Application Key**
   ```bash
   sudo -u fixit php artisan key:generate
   ```

5. **Set Permissions**
   ```bash
   sudo chown -R fixit:www-data storage bootstrap/cache
   sudo chmod -R 775 storage bootstrap/cache
   ```

### Web Server Configuration

#### Nginx Configuration

1. **Create Nginx Site Configuration**
   ```bash
   sudo nano /etc/nginx/sites-available/fixit-auto
   ```

2. **Add Configuration**
   ```nginx
   server {
       listen 80;
       listen [::]:80;
       server_name yourdomain.com www.yourdomain.com;
       root /var/www/fixit-auto-app/public;
       
       add_header X-Frame-Options "SAMEORIGIN";
       add_header X-Content-Type-Options "nosniff";
       add_header X-XSS-Protection "1; mode=block";
       
       index index.php;
       
       charset utf-8;
       
       location / {
           try_files $uri $uri/ /index.php?$query_string;
       }
       
       location = /favicon.ico { access_log off; log_not_found off; }
       location = /robots.txt  { access_log off; log_not_found off; }
       
       error_page 404 /index.php;
       
       location ~ \.php$ {
           fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
           fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
           include fastcgi_params;
       }
       
       location ~ /\.(?!well-known).* {
           deny all;
       }
       
       location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg)$ {
           expires 1y;
           add_header Cache-Control "public, immutable";
       }
   }
   ```

3. **Enable Site**
   ```bash
   sudo ln -s /etc/nginx/sites-available/fixit-auto /etc/nginx/sites-enabled/
   sudo nginx -t
   sudo systemctl reload nginx
   ```

#### Apache Configuration

1. **Create Virtual Host**
   ```bash
   sudo nano /etc/apache2/sites-available/fixit-auto.conf
   ```

2. **Add Configuration**
   ```apache
   <VirtualHost *:80>
       ServerName yourdomain.com
       ServerAlias www.yourdomain.com
       DocumentRoot /var/www/fixit-auto-app/public
       
       <Directory /var/www/fixit-auto-app/public>
           AllowOverride All
           Require all granted
       </Directory>
       
       ErrorLog ${APACHE_LOG_DIR}/error.log
       CustomLog ${APACHE_LOG_DIR}/access.log combined
   </VirtualHost>
   ```

3. **Enable Site**
   ```bash
   sudo a2ensite fixit-auto.conf
   sudo a2dissite 000-default.conf
   sudo systemctl reload apache2
   ```

### SSL Configuration

1. **Install Certbot**
   ```bash
   sudo apt install -y certbot python3-certbot-nginx
   # For Apache: sudo apt install -y certbot python3-certbot-apache
   ```

2. **Obtain SSL Certificate**
   ```bash
   sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
   ```

3. **Auto-renewal Setup**
   ```bash
   sudo certbot renew --dry-run
   ```

## Docker Deployment

### Docker Compose Setup

1. **Create Docker Compose File**
   ```yaml
   version: '3.8'
   
   services:
     app:
       build:
         context: .
         dockerfile: Dockerfile
       container_name: fixit-app
       restart: unless-stopped
       working_dir: /var/www
       volumes:
         - ./:/var/www
         - ./storage:/var/www/storage
       environment:
         - APP_ENV=production
         - APP_DEBUG=false
       networks:
         - fixit-network
       depends_on:
         - db
         - redis
   
     nginx:
       image: nginx:alpine
       container_name: fixit-nginx
       restart: unless-stopped
       ports:
         - "80:80"
         - "443:443"
       volumes:
         - ./:/var/www
         - ./docker/nginx:/etc/nginx/conf.d
         - ./docker/ssl:/etc/nginx/ssl
       networks:
         - fixit-network
       depends_on:
         - app
   
     db:
       image: mysql:8.0
       container_name: fixit-db
       restart: unless-stopped
       environment:
         MYSQL_DATABASE: fixit_auto
         MYSQL_ROOT_PASSWORD: ${DB_PASSWORD}
         MYSQL_USER: fixit_user
         MYSQL_PASSWORD: ${DB_PASSWORD}
       volumes:
         - dbdata:/var/lib/mysql
         - ./docker/mysql:/docker-entrypoint-initdb.d
       ports:
         - "3306:3306"
       networks:
         - fixit-network
       command: --default-authentication-plugin=mysql_native_password
   
     redis:
       image: redis:alpine
       container_name: fixit-redis
       restart: unless-stopped
       volumes:
         - redisdata:/data
       networks:
         - fixit-network
       command: redis-server --appendonly yes
   
     queue:
       build:
         context: .
         dockerfile: Dockerfile
       container_name: fixit-queue
       restart: unless-stopped
       working_dir: /var/www
       volumes:
         - ./:/var/www
       environment:
         - APP_ENV=production
       networks:
         - fixit-network
       command: php artisan queue:work --sleep=3 --tries=3
       depends_on:
         - app
         - redis
   
     scheduler:
       build:
         context: .
         dockerfile: Dockerfile
       container_name: fixit-scheduler
       restart: unless-stopped
       working_dir: /var/www
       volumes:
         - ./:/var/www
       environment:
         - APP_ENV=production
       networks:
         - fixit-network
       command: php artisan schedule:work
       depends_on:
         - app
   
   volumes:
     dbdata:
     redisdata:
   
   networks:
     fixit-network:
       driver: bridge
   ```

2. **Create Dockerfile**
   ```dockerfile
   FROM php:8.2-fpm-alpine
   
   RUN apk update && apk add \
       build-base \
       libpng-dev \
       libjpeg-turbo-dev \
       freetype-dev \
       libzip-dev \
       zip \
       jpegoptim optipng pngquant gifsicle \
       vim \
       unzip \
       git \
       curl \
       oniguruma-dev \
       libxml2-dev \
       mysql-client
   
   RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl
   
   RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
   
   RUN addgroup -g 1000 -S fixit && \
       adduser -u 1000 -S fixit -G fixit
   
   WORKDIR /var/www
   
   USER fixit
   
   COPY --chown=fixit:fixit . .
   
   RUN composer install --no-dev --optimize-autoloader
   
   CMD ["php-fpm"]
   ```

3. **Create Nginx Configuration**
   ```nginx
   server {
       listen 80;
       listen [::]:80;
       server_name localhost;
       root /var/www/public;
       
       add_header X-Frame-Options "SAMEORIGIN";
       add_header X-Content-Type-Options "nosniff";
       
       index index.php;
       
       charset utf-8;
       
       location / {
           try_files $uri $uri/ /index.php?$query_string;
       }
       
       location = /favicon.ico { access_log off; log_not_found off; }
       location = /robots.txt  { access_log off; log_not_found off; }
       
       error_page 404 /index.php;
       
       location ~ \.php$ {
           fastcgi_pass app:9000;
           fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
           include fastcgi_params;
       }
       
       location ~ /\.(?!well-known).* {
           deny all;
       }
   }
   ```

4. **Create Environment File**
   ```bash
   cp .env.example .env
   nano .env
   ```

5. **Build and Start Containers**
   ```bash
   docker-compose build
   docker-compose up -d
   ```

6. **Run Application Setup**
   ```bash
   docker-compose exec app php artisan key:generate
   docker-compose exec app php artisan migrate --seed
   docker-compose exec app php artisan storage:link
   ```

## Cloud Deployment

### AWS Deployment

1. **Create EC2 Instance**
   - Ubuntu 22.04 LTS
   - t3.medium or larger
   - Security groups: HTTP(80), HTTPS(443), SSH(22)

2. **Configure Elastic IP**
   - Allocate Elastic IP
   - Associate with EC2 instance

3. **Set Up RDS Database**
   - MySQL 8.0
   - Multi-AZ deployment
   - Automated backups

4. **Configure Elasticache**
   - Redis cluster for caching
   - Multi-AZ for high availability

5. **Set Up S3 for Storage**
   - Create S3 bucket
   - Configure for file storage
   - Set up lifecycle policies

6. **Configure Load Balancer**
   - Application Load Balancer
   - SSL termination
   - Health checks

### DigitalOcean Deployment

1. **Create Droplet**
   - Ubuntu 22.04
   - 4GB RAM, 2 vCPUs
   - 80GB SSD

2. **Set Up Managed Database**
   - MySQL cluster
   - Automated backups
   - Read replicas

3. **Configure Spaces**
   - Object storage for files
   - CDN enabled

4. **Set Up Load Balancer**
   - SSL termination
   - HTTP/2 support

### Google Cloud Deployment

1. **Create Compute Engine Instance**
   - Ubuntu 22.04
   - e2-medium or larger
   - Persistent disk

2. **Set Up Cloud SQL**
   - MySQL 8.0
   - High availability
   - Automated backups

3. **Configure Cloud Storage**
   - Bucket for file storage
   - Lifecycle management

4. **Set Up Load Balancing**
   - Global load balancer
   - SSL certificates

## Database Setup

### Migration

1. **Run Migrations**
   ```bash
   php artisan migrate
   ```

2. **Seed Database**
   ```bash
   php artisan db:seed
   ```

3. **Create Admin User**
   ```bash
   php artisan tinker
   ```
   ```php
   User::create([
       'name' => 'Admin User',
       'email' => 'admin@fixitauto.com',
       'password' => Hash::make('admin123'),
       'role' => 'admin'
   ]);
   ```

### Database Optimization

1. **Create Indexes**
   ```sql
   -- Example indexes for performance
   CREATE INDEX idx_vehicles_vin ON vehicles(vin);
   CREATE INDEX idx_customers_email ON customers(email);
   CREATE INDEX idx_invoices_status ON invoices(status);
   CREATE INDEX idx_work_orders_status ON work_orders(status);
   ```

2. **Configure MySQL Settings**
   ```ini
   [mysqld]
   innodb_buffer_pool_size = 1G
   innodb_log_file_size = 256M
   max_connections = 200
   query_cache_type = 1
   query_cache_size = 64M
   ```

3. **Regular