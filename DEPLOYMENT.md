# Doctix — Production Linux Server Deployment Guide

This guide covers deploying Doctix to a production Ubuntu 24.04 LTS / Debian 12 server with Nginx, PHP-FPM 8.3+, MySQL 8+, Redis, Supervisor, and Let's Encrypt SSL.

---

## 1. System Packages & Document Processing Dependencies

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y nginx mysql-server redis-server supervisor unzip git curl \
  php8.3-fpm php8.3-mysql php8.3-sqlite3 php8.3-xml php8.3-mbstring php8.3-curl \
  php8.3-zip php8.3-gd php8.3-imagick php8.3-bcmath php8.3-intl \
  ghostscript libreoffice-core libreoffice-writer libreoffice-calc libreoffice-impress \
  tesseract-ocr clamav
```

---

## 2. Application Deployment & Optimization

```bash
cd /var/www/doctix
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
php artisan storage:link
php artisan migrate --force --seed

# Cache configurations, routes, and views for production performance
php artisan config:cache
php artisan route:cache
php artisan view:cache

chown -R www-data:www-data /var/www/doctix/storage /var/www/doctix/bootstrap/cache
chmod -R 775 /var/www/doctix/storage /var/www/doctix/bootstrap/cache
```

---

## 3. Laravel Scheduler (Cron Configuration)

Add the Laravel scheduler cron entry so `doctix:maintenance` automatically purges expired temporary uploads/processed files and transitions expired subscriptions:

```cron
* * * * * cd /var/www/doctix && php artisan schedule:run >> /dev/null 2>&1
```

---

## 4. Queue Worker Configuration (Supervisor)

Create `/etc/supervisor/conf.d/doctix-worker.conf`:

```ini
[program:doctix-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/doctix/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/var/www/doctix/storage/logs/worker.log
stopwaitsecs=3600
```

Reload Supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start doctix-worker:*
```

---

## 5. Nginx Virtual Host & SSL Configuration

Create `/etc/nginx/sites-available/doctix`:

```nginx
server {
    listen 80;
    server_name doctix.io www.doctix.io;
    root /var/www/doctix/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    client_max_body_size 512M;

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable HTTPS with Certbot:
```bash
sudo ln -s /etc/nginx/sites-available/doctix /etc/nginx/sites-enabled/
sudo certbot --nginx -d doctix.io -d www.doctix.io
sudo systemctl reload nginx
```

---

## 6. Database & Storage Backups

Run automated daily MySQL dumps and encrypted S3 synchronization:
```bash
mysqldump -u doctix_user -p doctix | gzip > /backups/doctix-db-$(date +%F).sql.gz
```

