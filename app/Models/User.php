<?php

namespace App\Models;

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
    ];

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
        return $this->checkins()
            ->where('date', today($this->timezone ?: config('app.reminder_timezone')))
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