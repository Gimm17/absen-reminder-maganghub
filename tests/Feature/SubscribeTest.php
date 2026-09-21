<?php

namespace Tests\Feature;

use App\Models\PushEndpoint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscribeTest extends TestCase
{
    use RefreshDatabase;

    public function test_subscribe_creates_user_and_endpoint(): void
    {
        $payload = [
            'email' => 'magang1@test.com',
            'name'  => 'Magang Satu',
            'subscription' => [
                'endpoint' => 'https://fcm.googleapis.com/fcm/send/abc123',
                'keys' => [
                    'p256dh' => 'BEl9...',
                    'auth'   => 'a1b2...',
                ],
                'contentEncoding' => 'aesgcm',
            ],
        ];

        $r = $this->postJson('/api/subscribe', $payload);

        $r->assertStatus(201)
          ->assertJson(['ok' => true]);

        $this->assertDatabaseHas('users', ['email' => 'magang1@test.com']);
        $this->assertDatabaseHas('push_endpoints', [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/abc123',
        ]);
    }

    public function test_subscribe_updates_existing_endpoint(): void
    {
        $user = User::factory()->create();
        $endpoint = 'https://fcm.googleapis.com/fcm/send/xyz';

        $this->postJson('/api/subscribe', [
            'user_id' => $user->id,
            'subscription' => [
                'endpoint' => $endpoint,
                'keys' => ['p256dh' => 'old', 'auth' => 'old'],
            ],
        ])->assertStatus(201);

        $this->postJson('/api/subscribe', [
            'user_id' => $user->id,
            'subscription' => [
                'endpoint' => $endpoint,
                'keys' => ['p256dh' => 'new', 'auth' => 'new'],
            ],
        ])->assertStatus(201);

        $this->assertEquals(1, PushEndpoint::where('user_id', $user->id)->count());
        $row = PushEndpoint::where('user_id', $user->id)->first();
        $this->assertEquals('new', $row->p256dh);
    }

    public function test_subscribe_validates_required_fields(): void
    {
        $this->postJson('/api/subscribe', [])
             ->assertStatus(422);
    }
}