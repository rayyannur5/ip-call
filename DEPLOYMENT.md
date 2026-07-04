# Deployment Guide - IP Call v2 dengan XAMPP (Symlink)

## Arsitektur URL

| URL | Aplikasi |
|-----|----------|
| `localhost/ip-call/` | Redirect ke Admin |
| `localhost/ip-call/admin` | Laravel Admin Panel |
| `localhost/ip-call/server/*` | Laravel API |
| `localhost/ip-call/login` | Login Page |

## Cara Kerja

Symlink `ip-call` di htdocs mengarah ke folder `public` Laravel:

```
htdocs/
└── ip-call/              ← Symlink ke .../ip-call-project/ip-call-v2/public
    ├── index.php         (dari Laravel public)
    ├── .htaccess         (dari Laravel public)
    └── ...
```

Project structure:
```
/path/to/ip-call-project/           ← Folder project (di luar atau rename)
├── ip-call-v2/                     ← Laravel app
│   ├── app/
│   ├── public/                     ← Target symlink
│   ├── resources/
│   ├── routes/
│   └── ...
├── admin_backup/                   ← Backup PHP native lama
└── server_backup/                  ← Backup PHP native lama
```

## Langkah Deployment

### 1. Rename folder project (agar bisa buat symlink dengan nama ip-call)

```bash
cd /opt/lampp/htdocs
mv ip-call ip-call-project
```

### 2. Buat symlink

**Linux:**
```bash
ln -s ip-call-project/ip-call-v2/public ip-call
```

**Windows (CMD as Admin):**
```batch
mklink /D ip-call ip-call-project\ip-call-v2\public
```

### 3. Setup Laravel

```bash
cd /opt/lampp/htdocs/ip-call-project/ip-call-v2

# Install dependencies
composer install --optimize-autoloader --no-dev

# Setup environment
cp .env.example .env
```

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

Generate key:
```bash
php artisan key:generate
```

### 4. Set Permissions (Linux)

```bash
chmod -R 775 storage bootstrap/cache
sudo chown -R $USER:daemon storage bootstrap/cache
```

### 5. Clear Cache

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 6. Hapus folder PHP native lama

```bash
cd /opt/lampp/htdocs/ip-call-project
rm -rf admin server
# atau backup dulu: mv admin admin_backup && mv server server_backup
```

## Verifikasi

1. `http://localhost/ip-call/` → Redirect ke `/admin`
2. `http://localhost/ip-call/login` → Login page
3. `http://localhost/ip-call/admin` → Admin panel
4. `http://localhost/ip-call/server/device.php` → API JSON

## Troubleshooting

### Symlink tidak work di Windows
- Pastikan run CMD as Administrator
- Atau enable Developer Mode di Windows Settings

### 403 Forbidden
- Pastikan Apache bisa follow symlinks
- Edit `httpd.conf`:
```apache
<Directory "/opt/lampp/htdocs">
    Options +FollowSymLinks
    AllowOverride All
</Directory>
```

### 500 Error
```bash
tail -f storage/logs/laravel.log
php artisan cache:clear
```
