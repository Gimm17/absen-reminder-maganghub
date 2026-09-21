<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'timezone',
        'notify_slot_1',
        'notify_slot_2',
        'notify_slot_3',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'device_token',
    ];

    protected static function booted(): void
    {
        // Setiap user dapat device_token — dipakai Service Worker untuk
        // kirim checkin lewat tombol notifikasi (SW tidak bisa baca localStorage).
        static::creating(function (self $user) {
            if (empty($user->device_token)) {
                $user->device_token = \Illuminate\Support\Str::random(48);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'notify_slot_1' => 'boolean',
            'notify_slot_2' => 'boolean',
            'notify_slot_3' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function pushEndpoints(): HasMany
    {
        return $this->hasMany(PushEndpoint::class);
    }

    public function checkins(): HasMany
    {
        return $this->hasMany(Checkin::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function hasCheckedInToday(): bool
    {
        // Kolom `date` menyimpan string Y-m-d (bukan datetime). Wajib toDateString(),
        // kalau tidak Carbon di-bind jadi '2026-09-21 00:00:00' dan tidak pernah match.
        $today = Carbon::now($this->timezone ?: config('app.reminder_timezone'))->toDateString();

        return $this->checkins()
            ->where('date', $today)
            ->exists();
    }

    /**
     * Slots yg di-subscribe user ini (sesuai index toggle).
     */
    public function activeSlots(): array
    {
        $slots = [];
        if ($this->notify_slot_1) $slots[] = 'slot-1';
        if ($this->notify_slot_2) $slots[] = 'slot-2';
        if ($this->notify_slot_3) $slots[] = 'slot-3';
        return $slots;
    }
}