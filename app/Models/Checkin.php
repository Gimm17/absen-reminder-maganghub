<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Checkin extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'reported_at',
        'source',
    ];

    protected function casts(): array
    {
        return [
            // 'date' tidak di-cast — biarkan string Y-m-d murni untuk konsistensi lookup.
            'reported_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}