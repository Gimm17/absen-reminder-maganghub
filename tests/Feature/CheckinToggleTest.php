<?php

namespace Tests\Feature;

use App\Models\Checkin;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckinToggleTest extends TestCase
{
    use RefreshDatabase;

    public function test_delete_membatalkan_checkin_hari_ini(): void
    {
        $user = User::factory()->create();
        $this->postJson('/api/checkin', ['user_id' => $user->id])->assertStatus(201);

        $r = $this->deleteJson('/api/checkin', ['user_id' => $user->id]);

        $r->assertOk()
          ->assertJson(['ok' => true, 'deleted' => true]);

        $this->assertEquals(0, Checkin::where('user_id', $user->id)->count());
    }

    public function test_delete_tanpa_checkin_tetap_ok(): void
    {
        $user = User::factory()->create();

        $this->deleteJson('/api/checkin', ['user_id' => $user->id])
             ->assertOk()
             ->assertJson(['ok' => true, 'deleted' => false]);
    }

    public function test_toggle_bolak_balik(): void
    {
        $user = User::factory()->create();

        // catat
        $this->postJson('/api/checkin', ['user_id' => $user->id])->assertStatus(201);
        $this->getJson("/api/checkin/today?user_id={$user->id}")
             ->assertJsonPath('has_checked_in', true);

        // batalkan
        $this->deleteJson('/api/checkin', ['user_id' => $user->id])->assertOk();
        $this->getJson("/api/checkin/today?user_id={$user->id}")
             ->assertJsonPath('has_checked_in', false);

        // catat lagi — harus jalan, tidak kena unique constraint
        $this->postJson('/api/checkin', ['user_id' => $user->id])->assertStatus(201);
        $this->getJson("/api/checkin/today?user_id={$user->id}")
             ->assertJsonPath('has_checked_in', true);

        $this->assertEquals(1, Checkin::where('user_id', $user->id)->count());
    }

    public function test_delete_menolak_user_tidak_dikenal(): void
    {
        $this->deleteJson('/api/checkin', ['user_id' => 99999])->assertStatus(422);
    }

    public function test_delete_hanya_hari_ini_tidak_menyentuh_hari_lain(): void
    {
        $user = User::factory()->create();
        $today = Carbon::now('Asia/Makassar');

        // Checkin kemarin + hari ini
        Checkin::create([
            'user_id' => $user->id,
            'date' => $today->copy()->subDay()->toDateString(),
            'reported_at' => $today->copy()->subDay()->setTime(8, 0),
            'source' => 'button',
        ]);
        $this->postJson('/api/checkin', ['user_id' => $user->id])->assertStatus(201);

        $this->deleteJson('/api/checkin', ['user_id' => $user->id])->assertOk();

        // Yang kemarin harus tetap ada
        $this->assertEquals(1, Checkin::where('user_id', $user->id)->count());
        $this->assertDatabaseHas('checkins', [
            'user_id' => $user->id,
            'date' => $today->copy()->subDay()->toDateString(),
        ]);
    }
}