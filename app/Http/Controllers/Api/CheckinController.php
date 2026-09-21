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

    /**
     * GET /api/checkin/history?user_id=X&days=5
     *
     * Dipakai dashboard: strip "Histori Presensi N Hari Terakhir" + rekap mingguan.
     * Minggu kerja = Senin–Jumat (Sabtu/Minggu dihitung libur, tidak masuk penyebut).
     */
    public function history(Request $request): JsonResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'days'    => ['nullable', 'integer', 'min:3', 'max:30'],
        ]);

        $user = User::findOrFail($data['user_id']);
        $tz = $user->timezone ?: config('app.reminder_timezone');
        $days = $data['days'] ?? 5;

        $today = Carbon::now($tz)->startOfDay();

        // Rentang N hari terakhir, terbaru di kanan.
        $range = collect(range($days - 1, 0))
            ->map(fn ($ago) => $today->copy()->subDays($ago));

        $checkedDates = $user->checkins()
            ->whereBetween('date', [$range->first()->toDateString(), $range->last()->toDateString()])
            ->pluck('reported_at', 'date')
            ->toArray();

        $history = $range->map(function (Carbon $d) use ($checkedDates) {
            $key = $d->toDateString();
            $at = $checkedDates[$key] ?? null;

            return [
                'date'        => $key,
                'label'       => $d->locale('id')->isoFormat('ddd'),
                'label_full'  => $d->locale('id')->isoFormat('dddd, D MMM'),
                'is_weekend'  => $d->isWeekend(),
                'checked_in'  => (bool) $at,
                'time'        => $at ? Carbon::parse($at)->setTimezone(config('app.reminder_timezone'))->format('H:i') : null,
            ];
        });

        // Rekap minggu ini: Senin s/d hari ini, hanya hari kerja.
        $weekStart = Carbon::now($tz)->startOfWeek(Carbon::MONDAY)->startOfDay();
        $workdays = collect();
        for ($d = $weekStart->copy(); $d->lte($today); $d->addDay()) {
            if (! $d->isWeekend()) {
                $workdays->push($d->toDateString());
            }
        }

        $weekChecked = $user->checkins()
            ->whereIn('date', $workdays->all())
            ->count();

        $expected = $workdays->count();

        return response()->json([
            'user_id' => $user->id,
            'today'   => $today->toDateString(),
            'history' => $history,
            'week'    => [
                'checked'  => $weekChecked,
                'expected' => $expected,
                'rate'     => $expected > 0 ? round($weekChecked / $expected * 100) : 0,
            ],
        ]);
    }
}