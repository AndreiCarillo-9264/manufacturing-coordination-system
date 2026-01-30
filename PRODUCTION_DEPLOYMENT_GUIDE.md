PRODUCTION DEPLOYMENT GUIDE - INTRANET SERVER SETUP
═════════════════════════════════════════════════════════════════════════════════════════════════

For Real Server Testing (Apache/Nginx) with Multiple Concurrent Users

═════════════════════════════════════════════════════════════════════════════════════════════════

SECTION 1: SERVER REQUIREMENTS
──────────────────────────────

OPERATING SYSTEM:
  ✓ Ubuntu 20.04 LTS or 22.04 LTS (recommended)
  ✓ CentOS 8+ / Rocky Linux 8+
  ✓ Debian 10+
  ✓ Windows Server 2019+ (if required)

HARDWARE REQUIREMENTS:
  ✓ CPU: 2+ cores
  ✓ RAM: 4GB minimum (8GB recommended for multiple concurrent users)
  ✓ Disk: 50GB minimum
  ✓ Network: 1Gbps connection

SOFTWARE REQUIREMENTS:
  ✓ PHP 8.1+ (with extensions: bcmath, ctype, fileinfo, json, mbstring, openssl, pdo, pdo_mysql, tokenizer, xml)
  ✓ MySQL 8.0+ or MariaDB 10.6+
  ✓ Apache 2.4+ OR Nginx 1.20+
  ✓ Composer (PHP dependency manager)
  ✓ Node.js 16+ (for asset compilation)
  ✓ npm or yarn (Node package manager)

NETWORK REQUIREMENTS (INTRANET):
  ✓ Static IP address for server
  ✓ Internal network connectivity (192.168.x.x or similar)
  ✓ Firewall rules allowing HTTP/HTTPS access
  ✓ DNS entry or hosts file entry for easy access

═════════════════════════════════════════════════════════════════════════════════════════════════

SECTION 2: UBUNTU SERVER SETUP (Both Apache & Nginx)
─────────────────────────────────────────────────────

STEP 1: Update System
─────────────────────

```bash
sudo apt update && sudo apt upgrade -y
```

STEP 2: Install PHP and Extensions
───────────────────────────────────

```bash
sudo apt install -y php8.1 php8.1-cli php8.1-fpm php8.1-mysql php8.1-mbstring \
  php8.1-xml php8.1-bcmath php8.1-curl php8.1-zip php8.1-gd php8.1-json \
  php8.1-tokenizer php8.1-fileinfo php8.1-pdo php8.1-sqlite3 php8.1-intl
```

Verify PHP Installation:
```bash
php -v
```

STEP 3: Install MySQL
─────────────────────

```bash
sudo apt install -y mysql-server
```

Start MySQL:
```bash
sudo systemctl start mysql
sudo systemctl enable mysql
```

Secure MySQL (important for intranet):
```bash
sudo mysql_secure_installation
```

Follow prompts:
  • Root password: Set a strong password
  • Remove anonymous users: Y
  • Disable remote root login: Y (unless needed)
  • Remove test database: Y
  • Reload privileges: Y

STEP 4: Install Composer
────────────────────────

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
composer --version
```

STEP 5: Install Node.js and npm
────────────────────────────────

```bash
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs
node -v
npm -v
```

STEP 6: Install Git (if not present)
────────────────────────────────────

```bash
sudo apt install -y git
```

═══════════════════════════════════════════════════════════════════════════════════════════════

SECTION 3: APACHE SETUP (Option 1)
───────────────────────────────────

STEP 1: Install Apache
──────────────────────

```bash
sudo apt install -y apache2
```

STEP 2: Enable Required Modules
────────────────────────────────

```bash
sudo a2enmod rewrite
sudo a2enmod proxy
sudo a2enmod proxy_fcgi
sudo a2enmod setenvif
sudo a2enmod headers
sudo a2enmod http2
sudo a2enmod ssl
```

STEP 3: Configure PHP-FPM with Apache
──────────────────────────────────────

Edit Apache PHP-FPM config:
```bash
sudo nano /etc/apache2/conf-available/php8.1-fpm.conf
```

Add this content:
```apache
<FilesMatch \.php$>
    SetHandler "proxy:unix:/run/php/php8.1-fpm.sock|fcgi://localhost"
</FilesMatch>
```

Enable the config:
```bash
sudo a2enconf php8.1-fpm
```

STEP 4: Create Virtual Host
────────────────────────────

Create Apache config for the thesis system:
```bash
sudo nano /etc/apache2/sites-available/thesis-system.conf
```

Add this configuration:
```apache
<VirtualHost *:80>
    ServerName thesis-system.local
    ServerAlias thesis-system
    ServerAdmin admin@thesis-system.local
    
    DocumentRoot /var/www/thesis-system/public
    
    <Directory /var/www/thesis-system>
        AllowOverride All
        Require all granted
        
        <IfModule mod_rewrite.c>
            RewriteEngine On
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteCond %{REQUEST_FILENAME} !-d
            RewriteRule ^ index.php [QSA,L]
        </IfModule>
    </Directory>
    
    <Directory /var/www/thesis-system/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    # Logging
    ErrorLog ${APACHE_LOG_DIR}/thesis-system-error.log
    CustomLog ${APACHE_LOG_DIR}/thesis-system-access.log combined
    
    # Performance headers
    <IfModule mod_headers.c>
        Header set X-Content-Type-Options "nosniff"
        Header set X-Frame-Options "SAMEORIGIN"
        Header set X-XSS-Protection "1; mode=block"
    </IfModule>
</VirtualHost>
```

Enable the site:
```bash
sudo a2ensite thesis-system.conf
```

STEP 5: Enable HTTPS (Self-signed for Intranet)
────────────────────────────────────────────────

Create self-signed certificate:
```bash
sudo openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout /etc/ssl/private/thesis-system.key \
  -out /etc/ssl/certs/thesis-system.crt
```

Create HTTPS virtual host:
```bash
sudo nano /etc/apache2/sites-available/thesis-system-ssl.conf
```

Add:
```apache
<VirtualHost *:443>
    ServerName thesis-system.local
    ServerAlias thesis-system
    ServerAdmin admin@thesis-system.local
    
    DocumentRoot /var/www/thesis-system/public
    
    SSLEngine on
    SSLCertificateFile /etc/ssl/certs/thesis-system.crt
    SSLCertificateKeyFile /etc/ssl/private/thesis-system.key
    
    <Directory /var/www/thesis-system>
        AllowOverride All
        Require all granted
        
        <IfModule mod_rewrite.c>
            RewriteEngine On
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteCond %{REQUEST_FILENAME} !-d
            RewriteRule ^ index.php [QSA,L]
        </IfModule>
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/thesis-system-ssl-error.log
    CustomLog ${APACHE_LOG_DIR}/thesis-system-ssl-access.log combined
</VirtualHost>
```

Enable SSL site:
```bash
sudo a2ensite thesis-system-ssl.conf
```

STEP 6: Test and Restart Apache
────────────────────────────────

```bash
sudo apache2ctl configtest
```

Should output: "Syntax OK"

Restart Apache:
```bash
sudo systemctl restart apache2
sudo systemctl enable apache2
```

═══════════════════════════════════════════════════════════════════════════════════════════════

SECTION 4: NGINX SETUP (Option 2 - Recommended for Concurrent Users)
────────────────────────────────────────────────────────────────────

STEP 1: Install Nginx
─────────────────────

```bash
sudo apt install -y nginx
```

STEP 2: Create Application Directory
─────────────────────────────────────

```bash
sudo mkdir -p /var/www/thesis-system
sudo chown -R www-data:www-data /var/www/thesis-system
```

STEP 3: Configure Nginx Virtual Host
─────────────────────────────────────

Create Nginx config:
```bash
sudo nano /etc/nginx/sites-available/thesis-system
```

Add this configuration (optimized for concurrent users):
```nginx
upstream php_upstream {
    server unix:/run/php/php8.1-fpm.sock;
}

server {
    listen 80;
    listen [::]:80;
    
    server_name thesis-system.local thesis-system;
    root /var/www/thesis-system/public;
    index index.php index.html index.htm;
    
    # Logging
    access_log /var/log/nginx/thesis-system-access.log;
    error_log /var/log/nginx/thesis-system-error.log;
    
    # Gzip compression (improves performance)
    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css text/xml text/javascript 
               application/json application/javascript application/xml+rss;
    
    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    
    # Client timeout (important for large file uploads)
    client_max_body_size 50M;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass php_upstream;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_param PATH_INFO $fastcgi_path_info;
        
        # Performance tuning for concurrent users
        fastcgi_buffer_size 128k;
        fastcgi_buffers 256 4k;
        fastcgi_busy_buffers_size 256k;
        fastcgi_max_temp_file_size 2048m;
        fastcgi_temp_file_write_size 32k;
        fastcgi_read_timeout 600;
    }
    
    # Static files caching
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }
    
    # Deny access to hidden files
    location ~ /\. {
        deny all;
    }
}

# HTTPS/SSL configuration
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    
    server_name thesis-system.local thesis-system;
    root /var/www/thesis-system/public;
    index index.php index.html index.htm;
    
    ssl_certificate /etc/ssl/certs/thesis-system.crt;
    ssl_certificate_key /etc/ssl/private/thesis-system.key;
    
    # SSL Configuration
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;
    
    access_log /var/log/nginx/thesis-system-ssl-access.log;
    error_log /var/log/nginx/thesis-system-ssl-error.log;
    
    # Same configuration as HTTP block
    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css text/xml text/javascript 
               application/json application/javascript application/xml+rss;
    
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    client_max_body_size 50M;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass php_upstream;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_buffer_size 128k;
        fastcgi_buffers 256 4k;
        fastcgi_busy_buffers_size 256k;
        fastcgi_max_temp_file_size 2048m;
        fastcgi_temp_file_write_size 32k;
        fastcgi_read_timeout 600;
    }
    
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }
    
    location ~ /\. {
        deny all;
    }
}

# Redirect HTTP to HTTPS
server {
    listen 80;
    listen [::]:80;
    server_name thesis-system.local thesis-system;
    return 301 https://$server_name$request_uri;
}
```

Enable the site:
```bash
sudo ln -s /etc/nginx/sites-available/thesis-system /etc/nginx/sites-enabled/
```

Disable default site:
```bash
sudo unlink /etc/nginx/sites-enabled/default
```

Create self-signed certificate:
```bash
sudo openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout /etc/ssl/private/thesis-system.key \
  -out /etc/ssl/certs/thesis-system.crt
```

Test Nginx configuration:
```bash
sudo nginx -t
```

Should output: "syntax is ok" and "test is successful"

Start Nginx:
```bash
sudo systemctl start nginx
sudo systemctl enable nginx
```

═══════════════════════════════════════════════════════════════════════════════════════════════

SECTION 5: DEPLOY THESIS SYSTEM
───────────────────────────────

STEP 1: Clone or Copy Project
──────────────────────────────

If using git (recommended):
```bash
cd /var/www
sudo git clone https://github.com/your-repo/thesis-system.git
```

Or copy files:
```bash
sudo cp -r thesis-system /var/www/
```

STEP 2: Set Permissions
───────────────────────

```bash
sudo chown -R www-data:www-data /var/www/thesis-system
sudo chmod -R 755 /var/www/thesis-system
sudo chmod -R 775 /var/www/thesis-system/storage
sudo chmod -R 775 /var/www/thesis-system/bootstrap/cache
```

STEP 3: Install Dependencies
─────────────────────────────

```bash
cd /var/www/thesis-system
sudo -u www-data composer install --no-dev --optimize-autoloader
```

STEP 4: Copy Environment File
──────────────────────────────

```bash
sudo cp .env.example .env
```

Edit the .env file:
```bash
sudo nano .env
```

Important .env settings for production:
```env
APP_NAME="Thesis System"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://thesis-system.local

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=thesis_system
DB_USERNAME=thesis_user
DB_PASSWORD=strong_password_here

# Important: Set to false in production
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

# Reverb WebSocket for real-time (if using)
REVERB_HOST=thesis-system.local
REVERB_PORT=443
REVERB_SCHEME=https
```

STEP 5: Generate Application Key
─────────────────────────────────

```bash
sudo -u www-data php artisan key:generate
```

STEP 6: Create Database and User
─────────────────────────────────

Connect to MySQL:
```bash
sudo mysql -u root -p
```

Create database and user:
```sql
CREATE DATABASE thesis_system;
CREATE USER 'thesis_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON thesis_system.* TO 'thesis_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

STEP 7: Run Migrations
──────────────────────

```bash
cd /var/www/thesis-system
sudo -u www-data php artisan migrate --force
```

STEP 8: Seed Data (Optional)
─────────────────────────────

```bash
sudo -u www-data php artisan db:seed
```

STEP 9: Build Frontend Assets
──────────────────────────────

```bash
cd /var/www/thesis-system
npm install
npm run build
```

STEP 10: Clear Cache
────────────────────

```bash
sudo -u www-data php artisan cache:clear
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
```

═══════════════════════════════════════════════════════════════════════════════════════════════

SECTION 6: PHP-FPM OPTIMIZATION FOR CONCURRENT USERS
─────────────────────────────────────────────────────

Edit PHP-FPM configuration:
```bash
sudo nano /etc/php/8.1/fpm/pool.d/www.conf
```

Optimize these settings (for 10+ concurrent users):
```ini
; Increase worker processes
pm = dynamic
pm.max_children = 20           ; Max worker processes
pm.start_servers = 10          ; Initial number of workers
pm.min_spare_servers = 5       ; Minimum idle workers
pm.max_spare_servers = 15      ; Maximum idle workers
pm.max_requests = 500          ; Restart workers after 500 requests

; Request timeout
request_terminate_timeout = 300s

; Backlog for connection queue
listen.backlog = 1024
```

Restart PHP-FPM:
```bash
sudo systemctl restart php8.1-fpm
```

═══════════════════════════════════════════════════════════════════════════════════════════════

SECTION 7: CONFIGURE REVERB WEBSOCKET (FOR REAL-TIME UPDATES)
──────────────────────────────────────────────────────────────

Install Reverb:
```bash
cd /var/www/thesis-system
sudo -u www-data composer require laravel/reverb
sudo -u www-data php artisan reverb:install
```

Create Systemd service for Reverb:
```bash
sudo nano /etc/systemd/system/thesis-reverb.service
```

Add this configuration:
```ini
[Unit]
Description=Thesis System Reverb WebSocket Server
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/thesis-system
ExecStart=/usr/bin/php artisan reverb:start --host=0.0.0.0 --port=8080
Restart=always
RestartSec=5
StandardOutput=journal
StandardError=journal

[Install]
WantedBy=multi-user.target
```

Enable and start Reverb:
```bash
sudo systemctl daemon-reload
sudo systemctl enable thesis-reverb
sudo systemctl start thesis-reverb
```

Configure Nginx/Apache to proxy WebSocket:
For Nginx, add to your server block:
```nginx
location /app {
    proxy_pass http://127.0.0.1:8080;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "Upgrade";
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
    proxy_read_timeout 86400;
}
```

═══════════════════════════════════════════════════════════════════════════════════════════════

SECTION 8: CONFIGURE INTRANET ACCESS
─────────────────────────────────────

OPTION 1: Add to Hosts File (Client Machines)
──────────────────────────────────────────────

On Windows machines:
  Edit: C:\Windows\System32\drivers\etc\hosts
  Add: 192.168.X.X thesis-system.local

On Linux/Mac:
  Edit: /etc/hosts
  Add: 192.168.X.X thesis-system.local

OPTION 2: Configure DNS (Server)
─────────────────────────────────

Add A record in your DNS server:
```
thesis-system.local  A  192.168.X.X
```

OPTION 3: Access by IP
──────────────────────

Access directly: https://192.168.X.X

═══════════════════════════════════════════════════════════════════════════════════════════════

SECTION 9: FIREWALL CONFIGURATION
──────────────────────────────────

Allow HTTP and HTTPS:
```bash
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
```

Allow SSH (for remote management):
```bash
sudo ufw allow 22/tcp
```

Allow WebSocket (Reverb):
```bash
sudo ufw allow 8080/tcp
```

Enable firewall:
```bash
sudo ufw enable
```

═══════════════════════════════════════════════════════════════════════════════════════════════

SECTION 10: MULTI-USER CONCURRENT TESTING
───────────────────────────────────────────

BEFORE TESTING:
  ✓ Verify PHP-FPM workers are running: sudo systemctl status php8.1-fpm
  ✓ Verify Nginx/Apache is running: sudo systemctl status nginx (or apache2)
  ✓ Verify Reverb is running: sudo systemctl status thesis-reverb
  ✓ Check database is accessible: mysql -u thesis_user -p thesis_system

TEST 1: Basic Connectivity
──────────────────────────

1. Open browser and navigate to: https://thesis-system.local
2. Verify you can login
3. Check that dashboard loads without errors

TEST 2: Simultaneous Users
──────────────────────────

1. Open 5+ browser windows/tabs from different machines (if possible)
2. Have each user login with different accounts (create test users first)
3. Have different users perform actions simultaneously:
   - User A: Create a customer
   - User B: Create a product
   - User C: Create a job order
   - User D: Approve a job order
   - User E: View dashboards
4. Verify all actions complete without errors
5. Check that real-time updates work across all users

TEST 3: Real-Time Updates
─────────────────────────

1. Open Dashboard → Sales in two browser windows (side by side)
2. In window 1: Approve a job order
3. In window 2: Verify the dashboard updates without page refresh
4. Check that KPI counts update in real-time
5. Verify toast notifications appear

TEST 4: Performance Under Load
──────────────────────────────

Using Apache Bench or similar:
```bash
ab -c 10 -n 100 https://thesis-system.local/
```

Parameters:
  -c 10: 10 concurrent requests
  -n 100: 100 total requests

Expected results:
  - Response time: < 500ms for list pages
  - No 5xx errors
  - All requests succeed

TEST 5: Database Under Load
───────────────────────────

Monitor database performance:
```bash
watch -n 1 'mysqladmin -u thesis_user -p status'
```

Check for:
  - No connection errors
  - Acceptable query time
  - No table locks

═══════════════════════════════════════════════════════════════════════════════════════════════

SECTION 11: MONITORING AND LOGGING
──────────────────────────────────

CHECK NGINX LOGS:
```bash
sudo tail -f /var/log/nginx/thesis-system-access.log
sudo tail -f /var/log/nginx/thesis-system-error.log
```

CHECK APACHE LOGS:
```bash
sudo tail -f /var/log/apache2/thesis-system-error.log
sudo tail -f /var/log/apache2/thesis-system-access.log
```

CHECK LARAVEL LOGS:
```bash
sudo tail -f /var/www/thesis-system/storage/logs/laravel.log
```

CHECK PHP-FPM:
```bash
sudo systemctl status php8.1-fpm
sudo tail -f /var/log/php8.1-fpm.log
```

CHECK REVERB:
```bash
sudo systemctl status thesis-reverb
sudo journalctl -u thesis-reverb -f
```

═══════════════════════════════════════════════════════════════════════════════════════════════

SECTION 12: PERFORMANCE OPTIMIZATION
─────────────────────────────────────

CACHING:
  Update .env to use Redis/Memcached instead of file:
  ```env
  CACHE_DRIVER=redis
  SESSION_DRIVER=redis
  ```

DATABASE OPTIMIZATION:
  ✓ Create indexes on frequently queried columns
  ✓ Enable query caching in MySQL
  ✓ Run: ANALYZE TABLE table_name; periodically

LARAVEL OPTIMIZATION:
  ✓ Cache configuration: php artisan config:cache
  ✓ Cache routes: php artisan route:cache
  ✓ Optimize autoloader: composer install --optimize-autoloader

NETWORK OPTIMIZATION:
  ✓ Enable gzip compression (already in Nginx config)
  ✓ Enable browser caching (already configured for static files)
  ✓ Use CDN for static assets (optional)

═══════════════════════════════════════════════════════════════════════════════════════════════

SECTION 13: SECURITY HARDENING
──────────────────────────────

FIREWALL:
  ✓ Only allow necessary ports (80, 443, 22)
  ✓ Restrict SSH access to specific IPs if possible
  ✓ Block unused services

SSL/HTTPS:
  ✓ Use self-signed for intranet (already done)
  ✓ For production, use Let's Encrypt (free)
  ✓ Enable HSTS headers (already in configs)

DATABASE:
  ✓ Use strong passwords (already required)
  ✓ Bind MySQL to localhost only (default)
  ✓ Regular backups: mysqldump -u thesis_user -p thesis_system > backup.sql

FILE PERMISSIONS:
  ✓ Storage and bootstrap/cache writable by www-data
  ✓ .env file readable only by www-data
  ✓ No world-writable directories

LARAVEL SECURITY:
  ✓ APP_DEBUG=false in production ✓
  ✓ Use strong APP_KEY ✓
  ✓ CORS configured properly
  ✓ CSRF protection enabled ✓

═══════════════════════════════════════════════════════════════════════════════════════════════

SECTION 14: TROUBLESHOOTING
───────────────────────────

ERROR: 502 Bad Gateway
─ Check PHP-FPM status: sudo systemctl status php8.1-fpm
─ Check PHP-FPM socket: ls -la /run/php/php8.1-fpm.sock
─ Check error logs: tail -f /var/log/php8.1-fpm.log

ERROR: Permission Denied
─ Verify ownership: ls -la /var/www/thesis-system
─ Fix permissions: sudo chown -R www-data:www-data /var/www/thesis-system

ERROR: Database Connection Refused
─ Check MySQL status: sudo systemctl status mysql
─ Test connection: mysql -u thesis_user -p -h 127.0.0.1 thesis_system
─ Check .env DB_HOST is 127.0.0.1

ERROR: Real-Time Updates Not Working
─ Check Reverb status: sudo systemctl status thesis-reverb
─ Check port 8080 is open: sudo ss -tlnp | grep 8080
─ Check browser console for WebSocket errors

ERROR: Slow Response Times
─ Check system resources: free -m (RAM), df -h (disk)
─ Monitor PHP-FPM workers: ps aux | grep php-fpm
─ Check database query times: EXPLAIN SELECT...
─ Review application logs for slow operations

═══════════════════════════════════════════════════════════════════════════════════════════════

SECTION 15: MAINTENANCE AND UPDATES
────────────────────────────────────

REGULAR BACKUPS:
```bash
# Database backup
mysqldump -u thesis_user -p thesis_system > /backups/thesis-system-$(date +%Y%m%d).sql

# Project files backup
tar -czf /backups/thesis-system-$(date +%Y%m%d).tar.gz /var/www/thesis-system
```

PERIODIC TASKS:
```bash
# Clear old logs
sudo find /var/www/thesis-system/storage/logs -type f -mtime +30 -delete

# Clear cache
php artisan cache:clear
php artisan view:cache
```

UPDATE PROCEDURES:
```bash
# Pull latest code
cd /var/www/thesis-system
sudo git pull origin main

# Install dependencies
sudo composer install --optimize-autoloader

# Run migrations
php artisan migrate --force

# Clear cache
php artisan cache:clear

# Rebuild assets
npm install && npm run build
```

═══════════════════════════════════════════════════════════════════════════════════════════════

SECTION 16: COMPARISON: APACHE vs NGINX
────────────────────────────────────────

NGINX (Recommended for this project):
  ✓ Better performance under concurrent load
  ✓ Lower memory footprint
  ✓ Faster static file serving
  ✓ Built-in load balancing
  ✓ Less complex configuration
  ✓ Better suited for WebSocket (Reverb)

APACHE:
  ✓ More mature and widely supported
  ✓ Better .htaccess support
  ✓ More modules available
  ✓ Easier to learn for beginners
  - Higher resource usage
  - Slower with many concurrent connections

For your intranet with multiple concurrent users → NGINX is recommended

═══════════════════════════════════════════════════════════════════════════════════════════════

FINAL CHECKLIST BEFORE GOING LIVE
──────────────────────────────────

INFRASTRUCTURE:
  ☐ Server running with stable connection
  ☐ Firewall properly configured
  ☐ SSL certificate installed (self-signed OK for intranet)
  ☐ Backup strategy in place

SOFTWARE:
  ☐ All dependencies installed
  ☐ Database created and migrated
  ☐ Laravel configured (.env set)
  ☐ Application key generated
  ☐ Assets compiled (npm run build)
  ☐ Storage and cache directories writable

SECURITY:
  ☐ APP_DEBUG=false
  ☐ Database password is strong
  ☐ File permissions correct
  ☐ Firewall blocking unnecessary ports
  ☐ HTTPS enabled

TESTING:
  ☐ Single user login works
  ☐ All CRUD operations work
  ☐ Real-time updates working
  ☐ Multiple concurrent users tested
  ☐ Reports and PDF export working
  ☐ No errors in application logs

PERFORMANCE:
  ☐ Response times < 500ms
  ☐ PHP-FPM workers configured for concurrent load
  ☐ Database queries optimized
  ☐ Caching enabled

MONITORING:
  ☐ Log files accessible
  ☐ Daily backups scheduled
  ☐ System resources monitored
  ☐ Alert system configured (optional)

═══════════════════════════════════════════════════════════════════════════════════════════════

QUICK REFERENCE COMMANDS
────────────────────────

View server status:
```bash
sudo systemctl status nginx                    # Nginx
sudo systemctl status apache2                  # Apache
sudo systemctl status php8.1-fpm               # PHP-FPM
sudo systemctl status mysql                    # MySQL
sudo systemctl status thesis-reverb            # Reverb
```

Restart services:
```bash
sudo systemctl restart nginx                   # After config changes
sudo systemctl restart php8.1-fpm              # After pool.conf changes
sudo systemctl restart mysql                   # If needed
sudo systemctl restart thesis-reverb           # If WebSocket issues
```

View logs:
```bash
sudo tail -f /var/log/nginx/thesis-system-access.log
sudo tail -f /var/www/thesis-system/storage/logs/laravel.log
sudo journalctl -u thesis-reverb -f
```

Test configuration:
```bash
sudo nginx -t                                  # Nginx config test
sudo apache2ctl configtest                     # Apache config test
php artisan config:cache                       # Laravel config test
```

═══════════════════════════════════════════════════════════════════════════════════════════════

You're ready to deploy on a production server! Use NGINX for optimal performance with multiple
concurrent users. Follow the deployment steps carefully and test thoroughly before going live.

═════════════════════════════════════════════════════════════════════════════════════════════════
