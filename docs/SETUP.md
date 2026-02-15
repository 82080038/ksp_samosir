# Setup & Instalasi KSP Samosir

## Prasyarat

- PHP 8.0+
- MySQL 8.0+
- Apache dengan `mod_rewrite` aktif
- Ekstensi PHP: `mysqli`, `pdo_mysql`, `mbstring`, `json`, `openssl`

## Langkah Instalasi

### 1. Clone / Copy Project

```bash
cp -r ksp_samosir /var/www/html/
```

### 2. Konfigurasi Apache

Buat virtual host atau gunakan `.htaccess` yang sudah tersedia. Pastikan `AllowOverride All` aktif.

Contoh konfigurasi (lihat `ksp_samosir.conf`):

```apache
<VirtualHost *:80>
    DocumentRoot /var/www/html/ksp_samosir
    <Directory /var/www/html/ksp_samosir>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### 3. Konfigurasi Database

Edit `config/database.php` jika credential berbeda:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_NAME', 'ksp_samosir');
```

### 4. Buat Database & Jalankan Migrasi

```bash
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS ksp_samosir CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Schema utama
mysql -u root -p ksp_samosir < sql/accounting_tables.sql
mysql -u root -p ksp_samosir < sql/ai_credit_scoring_tables.sql
mysql -u root -p ksp_samosir < sql/digital_documents_tables.sql

# Sidebar dinamis
mysql -u root -p ksp_samosir < database/migrations/create_sidebar_menus_table.sql
mysql -u root -p ksp_samosir < database/migrations/seed_koperasi_sidebar_menus.sql

# Settings
mysql -u root -p ksp_samosir < database/migrations/add_settings_keys_20260215_v2.sql
```

### 5. Pastikan Direktori Writable

```bash
chmod -R 755 /var/www/html/ksp_samosir/logs
chmod -R 755 /var/www/html/ksp_samosir/public/uploads
chmod -R 755 /var/www/html/ksp_samosir/cache
```

### 6. Akses Aplikasi

Buka browser: `http://localhost/ksp_samosir/`

Login dengan akun development:

| Role | Username | Password |
|---|---|---|
| Admin | `admin` | `admin123` |
| Manager | `manager` | `manager123` |
| Staff | `staff` | `staff123` |
| Anggota | `anggota` | `anggota123` |

## Troubleshooting

- **500 Internal Server Error**: Pastikan `mod_rewrite` aktif dan `AllowOverride All` di Apache config.
- **Database connection failed**: Periksa credential di `config/database.php`.
- **Sidebar kosong**: Pastikan migrasi `create_sidebar_menus_table.sql` sudah dijalankan.
- **Permission denied pada logs/backup**: `chmod -R 755 logs/` dan pastikan user Apache punya akses.
