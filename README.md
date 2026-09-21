# Reminder Absen MagangHub

PWA Web Push untuk pengingat absen harian program MagangHub Kemnaker.

**Kirim 3x sehari** — pukul **16.30, 20.30, 23.00 WITA** (menjelang batas absen tengah malam) — notifikasi ke device user dengan tombol cepat: *Buka Dashboard Absen* + *Sudah Absen*.

## Stack

| Layer | Tech |
|---|---|
| Backend | Laravel 12 (PHP 8.2+) |
| Frontend | Vue 3 SPA + Vite |
| Database | SQLite (dev) / MySQL (cPanel) |
| Push | `minishlink/web-push` v11 (VAPID) |
| Cache/Queue | Laravel database driver |

## Quick Start (Local)

```bash
# 1. Install deps
composer install
npm install

# 2. Setup env
cp .env.example .env
php artisan key:generate

# 3. Database (SQLite)
php artisan migrate --force
php artisan db:seed --force

# 4. Generate VAPID keys
php artisan webpush:vapid
# edit .env, pastikan VAPID_SUBJECT=mailto:admin@yourdomain.com (atau https URL valid)

# 5. Build frontend
npm run build

# 6. Run scheduler (tiap menit cek slot)
php artisan schedule:work
```

Buka `http://localhost:8000` → daftar → aktifkan notifikasi.

### Cron cPanel

```
* * * * * cd /home/USER/public_html && php artisan schedule:run >> /dev/null 2>&1
```

Scheduler trigger `reminders:send slot-1` di 16:30, `slot-2` 20:30, `slot-3` 23:00 (WITA).

### Test push manual

```bash
# Dry-run (lihat user tanpa kirim push)
php artisan reminders:send slot-1 --dry-run

# Kirim beneran (perlu VAPID & endpoint subscribe)
php artisan reminders:send slot-1 --force

# Kirim ke user tertentu
php artisan reminders:send slot-1 --user=42
```

## Deployment ke cPanel

1. **Buat database MySQL** di cPanel → MySQL Databases. Catat nama, user, password.
2. **Upload file** (kecuali `vendor/`, `node_modules/`, `.env`):
   ```bash
   rsync -avz --exclude=vendor --exclude=node_modules --exclude=.env ./ user@host:~/public_html/
   ```
3. **Install di server**:
   ```bash
   ssh user@host
   cd ~/public_html
   composer install --no-dev --optimize-autoloader
   npm install && npm run build
   ```
4. **Setup `.env`**:
   ```
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://yourdomain.com
   
   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_PORT=3306
   DB_DATABASE=cpanel_dbname
   DB_USERNAME=cpanel_dbuser
   DB_PASSWORD=...
   
   VAPID_SUBJECT="mailto:admin@yourdomain.com"
   # (salin VAPID_PUBLIC_KEY & VAPID_PRIVATE_KEY dari local)
   
   CACHE_STORE=database
   QUEUE_CONNECTION=database
   SESSION_DRIVER=database
   ```
5. **Migrate**:
   ```bash
   php artisan migrate --force
   php artisan db:seed --force   # admin default
   php artisan storage:link
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
6. **DocumentRoot**: arahkan ke `public_html/public/` (atau copy isi `public/` ke `public_html/`).
7. **Cron**: tambahkan di cPanel Cron Jobs:
   ```
   * * * * * cd /home/USER/public_html && php artisan schedule:run >> /dev/null 2>&1
   ```
8. **HTTPS**: enable AutoSSL di cPanel (Let's Encrypt).

### Icon files (perlu dibuat)

`public/icons/icon-192.png` dan `icon-512.png` belum disertakan (untuk hindari binary di git).
Generate dari favicon dengan tool online (misal realfavicongenerator.net) atau pakai placeholder.

## Struktur

```
app/
  Console/Commands/
    SendReminderCommand.php    # Kirim push ke semua user
    GenerateVapidKeys.php      # Setup VAPID keypair
  Http/Controllers/
    Api/{Vapid,Subscribe,Checkin}Controller.php
    DashboardController.php    # SPA shell
    AdminController.php        # Admin dashboard + CSV export
  Models/{User,PushEndpoint,Checkin}.php
  Services/PushNotificationService.php  # VAPID wrapper + endpoint cleanup
config/webpush.php
database/migrations/2026_09_21_*.php
public/sw.js                  # Service Worker (root scope)
public/manifest.webmanifest
resources/
  js/
    App.vue
    views/{Register,Dashboard,Admin}.vue
    composables/usePushSubscription.js
  views/app.blade.php         # SPA mount point
tests/Feature/*.php
```

## Testing

```bash
php artisan test
```

10 tests:
- `tests/Unit/PlaceholderTest.php`
- `tests/Feature/CheckinTest.php` — 4 tests (create, second-checkin idempotent, unknown user, today endpoint)
- `tests/Feature/SubscribeTest.php` — 3 tests (create user+endpoint, update existing, validation)
- `tests/Feature/ReminderCommandTest.php` — 2 tests (dry-run, invalid slot)

## cara kerja Push

1. User buka PWA di Chrome/Safari Android → `navigator.serviceWorker.register('/sw.js')`.
2. User klik "Daftar" → Vue panggil `Notification.requestPermission()`.
3. Browser return `PushSubscription` (endpoint, p256dh, auth).
4. Vue POST ke `/api/subscribe` dengan subscription + email.
5. Server simpan di `push_endpoints` + welcome notification.
6. Cron trigger tiap jam sesuai → `PushNotificationService::sendToUser($user, $payload)`.
7. Server POST ke endpoint push (FCM/Mozilla autopush) dengan VAPID signature.
8. Browser terima → Service Worker `push` event → `showNotification()`.
9. User tap notifikasi → Service Worker `notificationclick` → buka tab dashboard / catat checkin.

## Troubleshooting

**VAPID_SUBJECT error**: pastikan `.env` punya `VAPID_SUBJECT=mailto:you@domain.com` (atau https URL yg valid). Apple push reject kalau subject invalid.

**Push gak sampai di Safari iOS**: cek iOS ≥ 16.4 dan user Add to Home Screen (push di iOS web hanya untuk installed PWA).

**Cron gak jalan di cPanel**: cek cron path absolut (`which php`). Gak perlu SSH — cPanel UI sudah ada.

**`unable to open database file` SQLite**: OneDrive/Dropbox sync bisa lock file. Pakai MySQL production.

## Lisensi

MIT. Boleh dipake untuk siapa aja.