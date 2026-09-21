<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Minishlink\WebPush\Subscription;

class PushEndpoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'endpoint',
        'p256dh',
        'auth',
        'content_encoding',
        'user_agent',
        'last_used_at',
    ];

    protected function casts(): array
    {
        return [
            'last_used_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Convert ke Subscription object minishlink/web-push.
     */
    public function toSubscription(): Subscription
    {
        return Subscription::create([
            'endpoint'        => $this->endpoint,
            'publicKey'       => $this->p256dh,
            'authToken'       => $this->auth,
            'contentEncoding' => $this->content_encoding ?: 'aesgcm',
        ]);
    }
}