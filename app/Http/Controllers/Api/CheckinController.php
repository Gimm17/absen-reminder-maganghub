<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Checkin;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CheckinController extends Controller
{
    /**
     * POST /api/checkin — catat self-report absen harian.
     * Body: { user_id: int, source?: 'button'|'manual' }
     *
     * Idempotent per (user_id, date).
     */
    public function store(Request $request): JsonResponse
    {
        $data = Validator::make($request->all(), [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'source'  => ['nullable', 'in:button,manual'],
        ])->validate();

        $user = User::findOrFail($data['user_id']);
        $tz = $user->timezone ?: config('app.reminder_timezone');
        $today = Carbon::now($tz)->toDateString();

        $checkin = Checkin::updateOrCreate(
            ['user_id' => $user->id, 'date' => $today],
            [
                'reported_at' => now(),
                'source'      => $data['source'] ?? 'button',
            ]
        );
        // Refresh from DB supaya wasRecentlyCreated akurat setelah edge-case composite key update.
        $checkin->refresh();
        $wasRecentlyCreated = $checkin->wasRecentlyCreated;

        $wasRecentlyCreated = $checkin->wasRecentlyCreated;

        return response()->json([
            'ok'         => true,
            'created'    => $wasRecentlyCreated,
            'checkin'    => $checkin,
            'message'    => $wasRecentlyCreated
                ? 'Absen hari ini tercatat. Terima kasih!'
                : 'Kamu sudah absen hari ini. Tidak ada perubahan.',
        ], $wasRecentlyCreated ? 201 : 200);
    }

    /**
     * GET /api/checkin/today?user_id=X — cek status hari ini.
     */
    public function today(Request $request): JsonResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $user = User::findOrFail($data['user_id']);
        $tz = $user->timezone ?: config('app.reminder_timezone');
        $today = Carbon::now($tz)->toDateString();

        $checkin = $user->checkins()->where('date', $today)->first();

        return response()->json([
            'user_id'    => $user->id,
            'date'       => $today,
            'has_checked_in' => (bool) $checkin,
            'checkin'    => $checkin,
        ]);
    }
}