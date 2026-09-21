<?php

namespace App\Http\Controllers;

use App\Models\Checkin;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminController extends Controller
{
    /**
     * Middleware 'auth' + 'admin' di route file. Method ini render SPA shell,
     * lalu Vue component nge-fetch data via endpoint admin.
     */
    public function index(Request $request): Renderable
    {
        return view('app');
    }

    /**
     * GET /api/admin/summary — agregat kepatuhan.
     * Dipanggil dari Vue admin view (CORS same-origin).
     */
    public function summary(Request $request)
    {
        $tz = config('app.reminder_timezone');
        $today = Carbon::now($tz)->toDateString();
        $monthStart = Carbon::now($tz)->startOfMonth()->toDateString();
        $daysInMonth = Carbon::now($tz)->daysInMonth;

        $users = User::where('is_active', true)->get();
        $totalUsers = $users->count();

        $checkedToday = Checkin::where('date', $today)->whereIn('user_id', $users->pluck('id'))->count();

        $monthCheckins = Checkin::whereIn('user_id', $users->pluck('id'))
            ->whereBetween('date', [$monthStart, $today])
            ->get()
            ->groupBy('user_id');

        $perUser = $users->map(function (User $u) use ($monthCheckins, $daysInMonth, $monthStart, $today) {
            $count = isset($monthCheckins[$u->id]) ? $monthCheckins[$u->id]->count() : 0;
            $expected = Carbon::parse($today)->day; // hari ke-…
            return [
                'id'      => $u->id,
                'name'    => $u->name,
                'email'   => $u->email,
                'checked' => $count,
                'expected'=> $expected,
                'rate'    => $expected > 0 ? round($count / $expected * 100, 1) : 0,
            ];
        });

        return response()->json([
            'total_users'      => $totalUsers,
            'checked_today'    => $checkedToday,
            'today_rate'       => $totalUsers > 0 ? round($checkedToday / $totalUsers * 100, 1) : 0,
            'per_user'         => $perUser,
            'period'           => ['start' => $monthStart, 'end' => $today, 'days' => $daysInMonth],
        ]);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $tz = config('app.reminder_timezone');
        $today = Carbon::now($tz)->toDateString();
        $monthStart = Carbon::now($tz)->startOfMonth()->toDateString();

        $users = User::where('is_active', true)->orderBy('name')->get();
        $checkins = Checkin::whereIn('user_id', $users->pluck('id'))
            ->whereBetween('date', [$monthStart, $today])
            ->get()
            ->groupBy('user_id');

        $filename = "absensi-{$monthStart}-to-{$today}.csv";

        return response()->streamDownload(function () use ($users, $checkins) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Nama', 'Email', 'Tanggal Checkin']);
            foreach ($users as $u) {
                $rows = $checkins[$u->id] ?? collect();
                if ($rows->isEmpty()) {
                    fputcsv($out, [$u->name, $u->email, '']);
                } else {
                    foreach ($rows as $r) {
                        $date = is_string($r->date) ? $r->date : $r->date->toDateString();
                        fputcsv($out, [$u->name, $u->email, $date]);
                    }
                }
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=utf-8']);
    }
}