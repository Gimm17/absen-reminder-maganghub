<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#1e40af">
    <meta name="description" content="Pengingat absen harian MagangHub Kemnaker">
    {{-- Path relatif tanpa leading slash — works di root domain dan subfolder. --}}
    <link rel="manifest" href="{{ url('manifest.webmanifest') }}">
    <link rel="icon" href="{{ url('icons/icon-192.png') }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ url('icons/icon-192.png') }}">
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
                // Path relatif terhadap base URL aplikasi. Service Worker HAREGAKU ada di scope aplikasi.
                navigator.serviceWorker.register('{{ url("sw.js") }}').catch(err => {
                    console.error('SW registration failed', err);
                });
            });
        }
    </script>
</body>
</html>