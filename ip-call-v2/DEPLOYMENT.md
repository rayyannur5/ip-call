# Deployment Guide - IP Call v2 dengan XAMPP

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
cd /path/to/ip-call-v2
composer install --optimize-autoloader --no-dev
```

### 3. Konfigurasi Environment
Copy `.env.example` ke `.env` dan sesuaikan:
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

Generate key:
```bash
php artisan key:generate
```

### 4. Optimasi Laravel
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 5. Buat Symlink di htdocs

**Windows (Run CMD as Administrator):**
```batch
cd C:\xampp\htdocs
mklink /D ip-call "C:\path\to\ip-call-v2\public"
```

**Linux:**
```bash
sudo ln -s /mnt/24DE6914DE68E012/Projects/ip-call/ip-call-v2/public /opt/lampp/htdocs/ip-call
```

### 6. Set Permissions (Linux only)
```bash
chmod -R 775 storage bootstrap/cache
sudo chown -R $USER:www-data storage bootstrap/cache
```

## Integrasi dengan React SPA

### Opsi A: React in Laravel Public (Recommended)
1. Build React app
2. Copy build files ke `ip-call-v2/public/`
3. Laravel akan serve `index.html` di route `/`
4. Admin panel di `/admin`
5. API di `/ip-call/server/*`

### Opsi B: React Terpisah di htdocs
```
htdocs/
├── index.html        ← React SPA
├── assets/           ← React assets  
└── ip-call/          ← Symlink ke Laravel public
```

Dengan setup ini:
- React SPA diakses via `http://localhost/`
- Laravel admin via `http://localhost/ip-call/admin`
- Legacy API via `http://localhost/ip-call/ip-call/server/*`

## Troubleshooting

### 500 Internal Server Error
- Check `storage/logs/laravel.log`
- Pastikan permissions sudah benar
- Pastikan `.env` sudah dikonfigurasi

### 404 Not Found
- Pastikan `mod_rewrite` enabled
- Check `.htaccess` ada di public folder

### Session/Cache Issues
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```
