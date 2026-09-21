<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#1e40af">
    <meta name="description" content="Pengingat absen harian MagangHub Kemnaker">
    {{-- Pakai path root-relative (leading slash). Karena subdomain = app root, gak perlu prefix lagi. --}}
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="icon" href="/icons/icon-192.png" type="image/png">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">
    <title>Reminder Absen MagangHub</title>
    @vite(['resources/js/app.js'])
</head>
<body>
    <div id="app"></div>
    <noscript>
        <p>Aplikasi butuh JavaScript. Mohon aktifkan JavaScript di browser Anda.</p>
    </noscript>
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                // SW di root path. Service Worker scope otomatis jadi parent dir sw.js.
                navigator.serviceWorker.register('/sw.js').catch(err => {
                    console.error('SW registration failed', err);
                });
            });
        }
    </script>
</body>
</html>