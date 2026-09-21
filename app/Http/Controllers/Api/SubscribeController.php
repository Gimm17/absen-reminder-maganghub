<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PushEndpoint;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class SubscribeController extends Controller
{
    /**
     * Simpan / update PushSubscription untuk user.
     *
     * Request body:
     *   - email (required kalau user baru)
     *   - name (required kalau user baru)
     *   - subscription.endpoint, .keys.p256dh, .keys.auth
     *   - subscription.contentEncoding (optional, default aesgcm)
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email'    => ['nullable', 'email'],
            'name'     => ['nullable', 'string', 'max:120'],
            'user_id'  => ['nullable', 'integer', 'exists:users,id'],
            'subscription.endpoint'                => ['required', 'string', 'max:1024'],
            'subscription.keys.p256dh'             => ['required', 'string', 'max:256'],
            'subscription.keys.auth'               => ['required', 'string', 'max:64'],
            'subscription.contentEncoding'         => ['nullable', 'string', 'max:32'],
        ]);

        $sub = $data['subscription'];

        // Determine user: existing user_id > email > new user
        $user = null;
        if (! empty($data['user_id'])) {
            $user = User::find($data['user_id']);
        } elseif (! empty($data['email'])) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'] ?? explode('@', $data['email'])[0],
                    'role' => 'user',
                ]
            );
        } else {
            return response()->json([
                'error' => 'email atau user_id wajib diisi.',
            ], 422);
        }

        // Upsert endpoint (satu device per user, endpoint yg sama = update).
        try {
            $endpoint = DB::transaction(function () use ($user, $sub, $request) {
                return PushEndpoint::updateOrCreate(
                    [
                        'user_id'  => $user->id,
                        'endpoint' => $sub['endpoint'],
                    ],
                    [
                        'p256dh'           => $sub['keys']['p256dh'],
                        'auth'             => $sub['keys']['auth'],
                        'content_encoding' => $sub['contentEncoding'] ?? 'aesgcm',
                        'user_agent'       => substr((string) $request->userAgent(), 0, 500),
                        'last_used_at'     => now(),
                    ]
                );
            });
        } catch (\Throwable $e) {
            Log::error('subscribe error', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Gagal menyimpan subscription.'], 500);
        }

        // Kirim welcome notification (opsional, hanya kalau user baru).
        if ($user->wasRecentlyCreated) {
            try {
                app(\App\Services\PushNotificationService::class)
                    ->sendToUser($user, [
                        'title' => 'Selamat datang!',
                        'body'  => 'Reminder absen MagangHub sudah aktif. Kamu akan dapat notif 3x sehari.',
                        'icon'  => '/icons/icon-192.png',
                        'data'  => ['url' => url('/dashboard')],
                    ]);
            } catch (\Throwable $e) {
                Log::warning('welcome push failed', ['error' => $e->getMessage()]);
            }
        }

        return response()->json([
            'ok'         => true,
            'user_id'    => $user->id,
            'endpoint_id'=> $endpoint->id,
            'message'    => 'Subscription berhasil disimpan.',
        ], 201);
    }
}