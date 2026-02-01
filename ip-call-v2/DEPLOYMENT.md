# Deployment Guide - IP Call v2 dengan XAMPP

## Arsitektur URL

| URL | Aplikasi |
|-----|----------|
| `localhost/ip-call/` | Redirect ke Admin |
| `localhost/ip-call/admin` | Laravel Admin Panel |
| `localhost/ip-call/server/*` | Laravel API (Legacy) |
| `localhost/ip-call/login` | Login Page |

## Struktur Folder

```
htdocs/
└── ip-call/                    ← Folder project di htdocs
    ├── index.php               ← Entry point (mengarah ke ip-call-v2)
    ├── .htaccess               ← Routing Laravel
    ├── ip-call-v2/             ← Laravel application
    │   ├── app/
    │   ├── bootstrap/
    │   ├── config/
    │   ├── public/             ← Assets Laravel (CSS, JS, images)
    │   ├── resources/
    │   ├── routes/
    │   ├── storage/
    │   └── vendor/
    └── ... (file lainnya)
```

**Folder yang dihapus setelah migrasi:**
- `admin/` ← PHP native lama (diganti Laravel)
- `server/` ← PHP native lama (diganti Laravel)

## Cara Kerja

1. Request masuk ke `htdocs/ip-call/`
2. `.htaccess` mengarahkan semua request ke `index.php`
3. `index.php` load Laravel dari folder `ip-call-v2/`
4. Laravel handle routing sesuai `routes/web.php`

## Prasyarat

- XAMPP dengan PHP >= 8.0
- Composer installed
- mod_rewrite enabled di Apache

## Langkah-langkah Deployment

### 1. Setup Database

```sql
CREATE DATABASE `ip-call`;
```

### 2. Install Laravel Dependencies

```bash
cd /opt/lampp/htdocs/ip-call/ip-call-v2
composer install --optimize-autoloader --no-dev
```

### 3. Konfigurasi Environment

Edit `ip-call-v2/.env`:

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

### 4. Set Permissions (Linux)

```bash
cd /opt/lampp/htdocs/ip-call/ip-call-v2
chmod -R 775 storage bootstrap/cache
sudo chown -R $USER:daemon storage bootstrap/cache
```

### 5. Optimasi untuk Production

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 6. Hapus Folder PHP Native Lama

```bash
cd /opt/lampp/htdocs/ip-call

# Backup dulu
mv admin admin_backup
mv server server_backup

# Setelah test OK, hapus
rm -rf admin_backup server_backup
```

## Verifikasi

Test URL berikut:

1. `http://localhost/ip-call/` → Redirect ke `/admin`
2. `http://localhost/ip-call/login` → Login page
3. `http://localhost/ip-call/admin` → Admin panel
4. `http://localhost/ip-call/server/device.php` → JSON (API)

## Assets Laravel

Assets (CSS, JS, images) dari Laravel ada di `ip-call-v2/public/`. 

Untuk mengakses assets di Blade template, gunakan:

```blade
<link href="{{ asset('css/app.css') }}" rel="stylesheet">
```

Laravel akan otomatis handle path yang benar.

## Troubleshooting

### 500 Internal Server Error

```bash
tail -f ip-call-v2/storage/logs/laravel.log
php artisan cache:clear
php artisan config:clear
```

### 404 Not Found

- Pastikan `mod_rewrite` enabled
- Pastikan `.htaccess` ada di folder `ip-call/`
- Pastikan `AllowOverride All` di httpd.conf

### Assets Tidak Load

Jika CSS/JS tidak load, cek path di Blade template. Assets harus di folder `ip-call-v2/public/`.

## Catatan React SPA

Jika ingin menambahkan React SPA:
1. React diakses dari URL terpisah (misal `localhost/ip-call-spa/`)
2. Atau React di-embed dalam Laravel sebagai frontend
