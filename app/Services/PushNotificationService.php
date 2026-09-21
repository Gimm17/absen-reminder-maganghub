<?php

namespace App\Services;

use App\Models\PushEndpoint;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\WebPush;

class PushNotificationService
{
    private ?WebPush $webPush = null;
    private bool $vapidChecked = false;

    public function __construct()
    {
        // Lazy: VAPID dicek hanya kalau beneran mau kirim push.
    }

    public function ensureVapidConfigured(): void
    {
        if ($this->vapidChecked) return;
        $this->vapidChecked = true;
        $publicKey = config('webpush.vapid.public_key');
        $privateKey = config('webpush.vapid.private_key');
        $vapidSubject = config('webpush.vapid.subject');

        if (! $publicKey || ! $privateKey || ! $vapidSubject) {
            throw new \RuntimeException(
                'VAPID belum dikonfigur. Jalankan: php artisan webpush:vapid, lalu tambahkan VAPID_SUBJECT=mailto:admin@domain ke .env'
            );
        }
    }

    private function client(): WebPush
    {
        $this->ensureVapidConfigured();
        if ($this->webPush) {
            return $this->webPush;
        }

        $this->webPush = new WebPush([
            'VAPID' => [
                'subject'    => config('webpush.vapid.subject'),
                'publicKey'  => config('webpush.vapid.public_key'),
                'privateKey' => config('webpush.vapid.private_key'),
            ],
            'client_options' => [
                'timeout' => 20,
                'allow_redirects' => false,
            ],
        ]);

        // Auto handle 404/410 Gone → hapus endpoint dari DB.
        $this->webPush->setReuseVAPIDHeaders(true);

        return $this->webPush;
    }

    /**
     * Kirim reminder ke SEMUA endpoint milik user.
     *
     * @return array{success: int, expired: int, failed: int}
     */
    public function sendToUser(User $user, array $payload): array
    {
        $this->ensureVapidConfigured();

        // Token per-perangkat disuntikkan di sini (payload dibuat sekali untuk
        // semua user, sedangkan token berbeda tiap user).
        $payload['data']['token'] = $user->device_token;

        $endpoints = $user->pushEndpoints()->get();

        if ($endpoints->isEmpty()) {
            return ['success' => 0, 'expired' => 0, 'failed' => 0];
        }

        $jsonPayload = json_encode($payload, JSON_UNESCAPED_UNICODE);
        $success = 0;
        $expired = 0;
        $failed = 0;

        foreach ($endpoints as $endpoint) {
            try {
                // sendOneNotification() SUDAH mengirim dan mengembalikan report-nya
                // (internal: queueNotification() + flush()->current()).
                // Jangan panggil flush() lagi — queue sudah kosong, hasilnya 0 semua.
                $report = $this->client()->sendOneNotification(
                    $endpoint->toSubscription(),
                    $jsonPayload,
                    ['TTL' => 3600, 'urgency' => 'normal', 'topic' => 'reminder-absen']
                );

                if ($report->isSubscriptionExpired()) {
                    // 404/410 — endpoint mati, hapus supaya tidak dicoba lagi.
                    $dead = $report->getRequest()->getUri()->__toString();
                    PushEndpoint::where('endpoint', $dead)->delete();
                    $expired++;
                    Log::info('push endpoint expired, dihapus', ['user_id' => $user->id]);
                } elseif ($report->isSuccess()) {
                    $success++;
                    $this->touchEndpoint($report->getEndpoint());
                } else {
                    $failed++;
                    Log::warning('push delivery failed', [
                        'user_id'         => $user->id,
                        'reason'          => $report->getReason(),
                        'response_status' => $report->getResponse()?->getStatusCode(),
                    ]);
                }
            } catch (\Throwable $e) {
                $failed++;
                Log::warning('push error', [
                    'user_id' => $user->id,
                    'error'   => $e->getMessage(),
                ]);
            }
        }

        return ['success' => $success, 'expired' => $expired, 'failed' => $failed];
    }

    /**
     * Bangun payload notification sesuai waktu slot.
     *
     * Catatan platform: Notification API TIDAK punya opsi `sound`.
     * Suara notifikasi selalu suara sistem (batas browser/OS, bukan
     * bisa diganti dari kode). Yang bisa diatur: `vibrate` (pola getar),
     * `silent`, `icon`, `badge`, `image`, dan `timestamp`.
     */
    public static function buildReminderPayload(string $slot): array
    {
        $dashboardUrl = config('app.maganghub_dashboard_url');
        $loginUrl = config('app.maganghub_login_url');

        $messages = [
            'slot-1' => [
                'title' => 'Absen MagangHub — 16.30 WITA',
                'body'  => 'Sore! Belum absen hari ini. Masih ada 7 jam 30 menit sebelum penutupan.',
                'tag'   => 'absen-slot-1',
                // Getar lembut — pengingat pertama, belum mendesak.
                'vibrate' => [180, 120, 180],
            ],
            'slot-2' => [
                'title' => 'Absen MagangHub — 20.30 WITA',
                'body'  => 'Malam! Absen hari ini belum tercatat. Sisa 3 jam 30 menit lagi.',
                'tag'   => 'absen-slot-2',
                // Getar sedang — mulai mendesak.
                'vibrate' => [300, 150, 300, 150, 300],
            ],
            'slot-3' => [
                'title' => 'Peringatan Terakhir — 23.00 WITA',
                'body'  => 'Absen tutup 1 jam lagi! Segera isi sebelum tengah malam.',
                'tag'   => 'absen-slot-3',
                // Getar kuat & panjang — peringatan terakhir.
                'vibrate' => [500, 200, 500, 200, 500, 200, 700],
            ],
        ];

        $msg = $messages[$slot] ?? $messages['slot-1'];
        $isLast = $slot === 'slot-3';

        return [
            'title'   => $msg['title'],
            'body'    => $msg['body'],
            'icon'    => '/icons/icon-192.png',
            'badge'   => '/icons/icon-96.png',
            'image'   => null,
            'tag'     => $msg['tag'],
            // renotify=true + tag sama -> notif lama diganti, tetap bunyi lagi.
            'renotify' => true,
            'requireInteraction' => $isLast,
            'silent'  => false,
            'vibrate' => $msg['vibrate'],
            'timestamp' => (int) (microtime(true) * 1000),
            'data' => [
                'url'       => $dashboardUrl,
                'login_url' => $loginUrl,
                'slot'      => $slot,
                'is_last'   => $isLast,
                'actions' => [
                    ['action' => 'checkin', 'title' => '✅ Sudah Absen'],
                    ['action' => 'open-dashboard', 'title' => 'Buka Portal'],
                ],
            ],
        ];
    }

    private function touchEndpoint(string $endpoint): void
    {
        PushEndpoint::where('endpoint', $endpoint)->update(['last_used_at' => now()]);
    }
}