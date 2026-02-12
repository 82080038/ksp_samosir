# Setup Guide - KSP Samosir Cooperative Management System

## Prerequisites
- Apache 2.4+ with PHP 8.0+
- MySQL 5.7+ or MariaDB 10.2+
- Composer (optional, for dependency management)

## Quick Setup

### 1) Database Setup
```sql
-- Create main database
CREATE DATABASE ksp_samosir;

-- Create/import address database (if not exists)
CREATE DATABASE alamat_db;
-- Import Indonesian address data or use existing alamat_db

-- Import main schema
mysql -u root -p ksp_samosir < sql/ksp_samosir.sql

-- Import additional schemas (optional but recommended)
mysql -u root -p ksp_samosir < database/role_management.sql
mysql -u root -p ksp_samosir < database/koperasi_accounting.sql
mysql -u root -p ksp_samosir < database/koperasi_activities.sql
```

### 2) Apache Configuration
Create Apache virtual host:

```apache
<VirtualHost *:80>
    ServerName localhost
    DocumentRoot /var/www/html/ksp_samosir
    
    <Directory /var/www/html/ksp_samosir>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/ksp_samosir_error.log
    CustomLog ${APACHE_LOG_DIR}/ksp_samosir_access.log combined
</VirtualHost>
```

### 3) File Permissions
```bash
# Set proper permissions
sudo chown -R www-data:www-data /var/www/html/ksp_samosir
sudo chmod -R 755 /var/www/html/ksp_samosir
sudo chmod -R 777 /var/www/html/ksp_samosir/cache
sudo chmod -R 777 /var/www/html/ksp_samosir/logs
```

### 4) Configuration
Edit `config/config.php`:

```php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'ksp_samosir');
define('DB_USER', 'root');
define('DB_PASS', 'your_password');

// Application URL
define('APP_URL', 'http://localhost/ksp_samosir');

// Development mode (set to false for production)
define('DEVELOPMENT_MODE', true);
```

### 5) Apache Restart
```bash
sudo systemctl restart apache2
# or
sudo service apache2 restart
```

## Access the Application
Open browser: **http://localhost/ksp_samosir/**

## Default Credentials (Development Mode)
- **Username**: admin
- **Password**: admin123

*Note: Authentication is disabled in development mode. All pages are accessible without login.*

## Production Setup

### 1) Enable Authentication
Uncomment authentication checks in `index.php`:
```php
// Authentication check
$public_pages = ['login', 'logout', 'register'];
if (!in_array($page, $public_pages) && !isLoggedIn()) {
    redirect('login');
}
```

### 2) Enable Role Checks
Uncomment role checks in controllers:
```php
$this->ensureLoginAndRole(['admin', 'staff']);
```

### 3) Enable Compliance
Uncomment compliance validation:
```php
validateTransactionCompliance($data['jenis_pinjaman_id'], $data['jumlah_pinjaman'], $data['anggota_id']);
```

### 4) Security Settings
```php
// In config/config.php
define('DEVELOPMENT_MODE', false);
define('SESSION_LIFETIME', 7200); // 2 hours
```

### 5) Database Security
```sql
-- Create dedicated database user
CREATE USER 'ksp_samosir'@'localhost' IDENTIFIED BY 'strong_password';
GRANT ALL PRIVILEGES ON ksp_samosir.* TO 'ksp_samosir'@'localhost';
GRANT SELECT ON alamat_db.* TO 'ksp_samosir'@'localhost';
FLUSH PRIVILEGES;
```

## Troubleshooting

### 404 Errors
- Check Apache configuration
- Verify .htaccess is enabled: `AllowOverride All`
- Check mod_rewrite is enabled: `sudo a2enmod rewrite`

### Database Connection Issues
- Verify MySQL/MariaDB is running
- Check credentials in config.php
- Ensure database exists: `SHOW DATABASES LIKE 'ksp_samosir';`

### Permission Issues
- Check file ownership: `ls -la /var/www/html/ksp_samosir`
- Fix permissions: `sudo chown -R www-data:www-data /var/www/html/ksp_samosir`

### Session Issues
- Check PHP session path: `php -i | grep session.save_path`
- Ensure session directory is writable

## Features Overview

### Core Modules
- **Dashboard**: Statistics and activity overview
- **Anggota**: Member management with address integration
- **Simpanan**: Savings management with interest calculation
- **Pinjaman**: Loan management with approval workflow
- **Produk**: Product catalog management
- **Penjualan**: Sales transaction management
- **Laporan**: Financial and activity reports
- **Settings**: Cooperative parameters and configuration

### Advanced Features
- **Role Management**: 5 roles with 18 granular permissions
- **Accounting System**: COA, journals, SHU calculation
- **Address Management**: Indonesian address database integration
- **Compliance System**: UU 25/1992 compliant operations
- **Responsive Design**: Mobile-friendly Bootstrap 5 interface
- **AJAX Integration**: Real-time data loading with modals

### Database Structure
- **ksp_samosir**: Main application database
- **alamat_db**: Indonesian address database (external)
- 15+ tables for complete cooperative operations

## Support
- Check `docs/` folder for detailed documentation
- Review `docs/DISABLED_FEATURES_DEV.md` for development vs production differences
- Use browser developer tools for debugging AJAX requests

## Development Notes
- Authentication disabled for easy development flow testing
- All modules accessible without login in development mode
- Mobile navigation fully functional with Bootstrap 5
- Address data sourced from external alamat_db database
