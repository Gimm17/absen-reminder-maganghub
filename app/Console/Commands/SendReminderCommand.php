<?php

namespace App\Console\Commands;

use App\Mail\ReminderAbsenMail;
use App\Models\User;
use App\Services\PushNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendReminderCommand extends Command
{
    protected $signature = 'reminders:send {slot : slot-1|slot-2|slot-3} {--dry-run : log only, do not send} {--user= : send to specific user_id} {--force : bypass "last run" guard}';

    protected $description = 'Kirim push reminder absen ke user aktif untuk slot tertentu.';

    /** Email dikirim hanya kalau MAIL_MAILER bukan 'log'/'array' (yaitu benar-benar terkonfigurasi). */
    private function emailEnabled(): bool
    {
        return ! in_array(config('mail.default'), ['log', 'array', 'null'], true)
            && ! empty(config('mail.from.address'));
    }

    public function handle(PushNotificationService $push): int
    {
        $slot = $this->argument('slot');
        if (! in_array($slot, ['slot-1', 'slot-2', 'slot-3'], true)) {
            $this->error("Slot tidak valid: {$slot}");
            return self::FAILURE;
        }

        // Guard: jangan kirim 2x untuk slot yg sama di hari yg sama (cron double-fire safe).
        $cacheKey = "reminder_sent:{$slot}:" . now()->toDateString();
        if (! $this->option('force') && Cache::has($cacheKey)) {
            $this->info("Reminder {$slot} sudah pernah jalan hari ini. Lewati (pakai --force untuk bypass).");
            return self::SUCCESS;
        }

        $columnMap = ['slot-1' => 'notify_slot_1', 'slot-2' => 'notify_slot_2', 'slot-3' => 'notify_slot_3'];
        $column = $columnMap[$slot] ?? 'notify_slot_1';

        $query = User::where('is_active', true)->where($column, true);

        if ($userId = $this->option('user')) {
            $query->where('id', $userId);
        }

        $users = $query->with('pushEndpoints')->get();
        $payload = PushNotificationService::buildReminderPayload($slot);

        if ($this->option('dry-run')) {
            $this->info("[DRY-RUN] Slot: {$slot}");
            $this->info("[DRY-RUN] User count: " . $users->count());
            $this->info("[DRY-RUN] Payload (preview):");
            $payloadPreview = [
                'title' => $payload['title'],
                'body'  => $payload['body'],
                'actions_count' => count($payload['data']['actions'] ?? []),
            ];
            $this->line(json_encode($payloadPreview, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            return self::SUCCESS;
        }

        $successTotal = 0;
        $expiredTotal = 0;
        $failedTotal = 0;

        $skippedTotal = 0;
        $emailSent = 0;
        $emailFailed = 0;

        foreach ($users as $user) {
            // Skip SEBELUM kirim — user yg sudah lapor absen hari ini tidak perlu diingatkan lagi.
            if ($user->hasCheckedInToday()) {
                $skippedTotal++;
                continue;
            }

            try {
                $result = $push->sendToUser($user, $payload);
                $successTotal += $result['success'];
                $expiredTotal += $result['expired'];
                $failedTotal  += $result['failed'];
            } catch (\Throwable $e) {
                $failedTotal++;
                Log::error('reminder push failed', [
                    'user_id' => $user->id,
                    'slot'    => $slot,
                    'error'   => $e->getMessage(),
                ]);
            }

            // Email sebagai channel kedua — jalan walau push gagal/tidak ada endpoint.
            if ($this->emailEnabled() && $user->email && ! $user->isAdmin()) {
                try {
                    Mail::to($user->email)->send(new ReminderAbsenMail(
                        $user,
                        $slot,
                        $payload['title'],
                        $payload['body'],
                    ));
                    $emailSent++;
                } catch (\Throwable $e) {
                    $emailFailed++;
                    Log::error('reminder email failed', [
                        'user_id' => $user->id,
                        'email'   => $user->email,
                        'slot'    => $slot,
                        'error'   => $e->getMessage(),
                    ]);
                }
            }
        }

        Cache::put('last_reminder_run', [
            'slot'        => $slot,
            'at'          => now()->toIso8601String(),
            'success'     => $successTotal,
            'expired'     => $expiredTotal,
            'failed'      => $failedTotal,
            'skipped'     => $skippedTotal,
            'email_sent'  => $emailSent,
            'email_failed'=> $emailFailed,
        ], now()->addDays(2));
        Cache::put($cacheKey, true, now()->addDay());

        $this->info("Reminder {$slot}: push_success={$successTotal} push_expired={$expiredTotal} push_failed={$failedTotal} skipped={$skippedTotal} email_sent={$emailSent} email_failed={$emailFailed} (total_users=" . $users->count() . ")");
        Log::info('reminder sent', compact('slot', 'successTotal', 'expiredTotal', 'failedTotal', 'skippedTotal', 'emailSent', 'emailFailed'));

        return self::SUCCESS;
    }
}