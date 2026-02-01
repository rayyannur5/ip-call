# Deployment Guide - IP Call v2 dengan XAMPP

## Arsitektur URL

| URL | Aplikasi |
|-----|----------|
| `localhost/` | React SPA |
| `localhost/ip-call/admin` | Laravel Admin Panel |
| `localhost/ip-call/server/*` | Laravel API (Legacy) |
| `localhost/ip-call/login` | Login Page |

## Struktur htdocs

```
htdocs/
├── index.html           ← React SPA (build)
├── assets/              ← React assets
├── static/              ← React static files (jika ada)
└── ip-call/             ← Symlink ke ip-call-v2/public
    ├── index.php
    ├── .htaccess
    └── ...
```

## Prasyarat

- XAMPP dengan PHP >= 8.0
- Composer installed
- mod_rewrite enabled di Apache

## Langkah-langkah Deployment

### 1. Setup Database

```sql
CREATE DATABASE `ip-call`;
```

Import database jika ada.

### 2. Install Dependencies

```bash
cd /mnt/24DE6914DE68E012/Projects/ip-call/ip-call-v2
composer install --optimize-autoloader --no-dev
```

### 3. Konfigurasi Environment

Edit `.env`:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=http://localhost/ip-call

DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ip-call
DB_USERNAME=root
DB_PASSWORD=
```

Generate key (jika belum):

```bash
php artisan key:generate
```

### 4. Optimasi Laravel untuk Production

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 5. Build React SPA

```bash
cd /path/to/react-spa
npm run build
```

Copy hasil build ke htdocs:

```bash
# Copy semua file build ke htdocs root
cp -r dist/* /opt/lampp/htdocs/
# atau untuk Windows
# copy /Y dist\* C:\xampp\htdocs\
```

### 6. Buat Symlink Laravel ke htdocs

**Linux:**

```bash
sudo ln -s /mnt/24DE6914DE68E012/Projects/ip-call/ip-call-v2/public /opt/lampp/htdocs/ip-call
```

**Windows (Run CMD as Administrator):**

```batch
mklink /D "C:\xampp\htdocs\ip-call" "D:\Projects\ip-call\ip-call-v2\public"
```

### 7. Set Permissions (Linux only)

```bash
cd /mnt/24DE6914DE68E012/Projects/ip-call/ip-call-v2
chmod -R 775 storage bootstrap/cache
sudo chown -R $USER:www-data storage bootstrap/cache
```

## Konfigurasi Apache (Opsional)

Jika ada masalah dengan `.htaccess`, edit `httpd.conf`:

```apache
<Directory "/opt/lampp/htdocs">
    AllowOverride All
    Require all granted
</Directory>
```

Pastikan `mod_rewrite` aktif:

```apache
LoadModule rewrite_module modules/mod_rewrite.so
```

## Verifikasi Deployment

Setelah deployment, test URL berikut:

1. `http://localhost/` → Harus menampilkan React SPA
2. `http://localhost/ip-call/login` → Harus menampilkan login Laravel
3. `http://localhost/ip-call/admin` → Redirect ke login (jika belum login)
4. `http://localhost/ip-call/server/device.php` → Harus return JSON (API)

## Troubleshooting

### 500 Internal Server Error

```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Clear semua cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### 404 Not Found

- Pastikan `mod_rewrite` enabled
- Pastikan `.htaccess` ada di public folder
- Cek symlink sudah benar

### Session/CSRF Issues

Pastikan `APP_URL` di `.env` sudah benar:

```env
APP_URL=http://localhost/ip-call
```

## Update dari PHP Native

Karena route API sudah menggunakan path yang sama (`/ip-call/server/*`), device yang sudah terhubung tidak perlu di-reconfigure. Cukup ganti folder `ip-call` lama dengan symlink ke Laravel public.

### Backup Dulu!

```bash
# Backup folder lama
mv /opt/lampp/htdocs/ip-call /opt/lampp/htdocs/ip-call-backup

# Buat symlink baru
sudo ln -s /mnt/24DE6914DE68E012/Projects/ip-call/ip-call-v2/public /opt/lampp/htdocs/ip-call
```
