<?php

namespace Tests\Feature;

use App\Models\Checkin;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckinTest extends TestCase
{
    use RefreshDatabase;

    public function test_first_checkin_today_creates_row(): void
    {
        $user = User::factory()->create();

        $r = $this->postJson('/api/checkin', [
            'user_id' => $user->id,
            'source'  => 'button',
        ]);

        $r->assertStatus(201)
          ->assertJson(['ok' => true, 'created' => true]);

        $today = Carbon::now(config('app.reminder_timezone'))->toDateString();
        $this->assertDatabaseHas('checkins', [
            'user_id' => $user->id,
            'date'    => $today,
        ]);
    }

    public function test_second_checkin_same_day_is_idempotent(): void
    {
        $user = User::factory()->create();
        $this->postJson('/api/checkin', ['user_id' => $user->id]);

        $r = $this->postJson('/api/checkin', ['user_id' => $user->id]);

        $r->assertStatus(200)
          ->assertJson(['ok' => true, 'created' => false]);

        $this->assertEquals(1, Checkin::where('user_id', $user->id)->count());
    }

    public function test_checkin_returns_422_for_unknown_user(): void
    {
        $this->postJson('/api/checkin', ['user_id' => 99999])
             ->assertStatus(422);
    }

    public function test_today_endpoint_reports_status(): void
    {
        $user = User::factory()->create();

        $r = $this->getJson("/api/checkin/today?user_id={$user->id}");
        $r->assertOk()->assertJson(['has_checked_in' => false]);

        $this->postJson('/api/checkin', ['user_id' => $user->id]);

        $r = $this->getJson("/api/checkin/today?user_id={$user->id}");
        $r->assertOk()->assertJson(['has_checked_in' => true]);
    }
}