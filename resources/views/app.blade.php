<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#1e40af">
    <meta name="description" content="Pengingat absen harian MagangHub Kemnaker">
    {{-- Path root-relative (leading slash). Subdomain = app root, tanpa prefix. --}}
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="icon" href="/icons/icon-192.png" type="image/png">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">

    {{-- Precision Presence typography --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">

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