<?php

namespace Tests\Feature;

use App\Models\Checkin;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HistoryTest extends TestCase
{
    use RefreshDatabase;

    private function makeCheckin(User $user, Carbon $date): void
    {
        Checkin::create([
            'user_id'     => $user->id,
            'date'        => $date->toDateString(),
            'reported_at' => $date->copy()->setTime(8, 14),
            'source'      => 'button',
        ]);
    }

    public function test_history_mengembalikan_n_hari_terakhir(): void
    {
        $user = User::factory()->create();
        $today = Carbon::now('Asia/Makassar')->startOfDay();

        $this->makeCheckin($user, $today);
        $this->makeCheckin($user, $today->copy()->subDays(2));

        $r = $this->getJson("/api/checkin/history?user_id={$user->id}&days=5");

        $r->assertOk()
          ->assertJsonCount(5, 'history')
          ->assertJsonPath('history.4.date', $today->toDateString())
          ->assertJsonPath('history.4.checked_in', true)
          ->assertJsonPath('history.2.checked_in', true)
          ->assertJsonPath('history.3.checked_in', false);
    }

    public function test_history_menghitung_rasio_mingguan_hari_kerja(): void
    {
        $user = User::factory()->create();
        $today = Carbon::now('Asia/Makassar')->startOfDay();

        // Catat semua hari kerja sejak Senin minggu ini sampai hari ini.
        $cursor = $today->copy()->startOfWeek(Carbon::MONDAY);
        while ($cursor->lte($today)) {
            if (! $cursor->isWeekend()) {
                $this->makeCheckin($user, $cursor);
            }
            $cursor->addDay();
        }

        $r = $this->getJson("/api/checkin/history?user_id={$user->id}");
        $r->assertOk();

        $week = $r->json('week');
        $this->assertGreaterThan(0, $week['expected'], 'Harus ada minimal 1 hari kerja.');
        $this->assertEquals(100, $week['rate'], 'Semua hari kerja tercatat -> rate 100%.');
        $this->assertEquals($week['expected'], $week['checked']);
    }

    public function test_weekend_ditandai_di_history(): void
    {
        $user = User::factory()->create();

        $this->getJson("/api/checkin/history?user_id={$user->id}&days=7")
             ->assertOk()
             ->assertJsonFragment(['is_weekend' => true]);
    }

    public function test_history_menolak_user_tidak_dikenal(): void
    {
        $this->getJson('/api/checkin/history?user_id=99999')->assertStatus(422);
    }

    public function test_ganti_email_berhasil(): void
    {
        $user = User::factory()->create(['email' => 'lama@test.com']);

        $this->postJson('/api/user/email', [
            'user_id' => $user->id,
            'email'   => 'baru@test.com',
        ])->assertOk()->assertJsonPath('email', 'baru@test.com');

        $this->assertDatabaseHas('users', ['id' => $user->id, 'email' => 'baru@test.com']);
    }

    public function test_ganti_email_menolak_email_dipakai_user_lain(): void
    {
        $a = User::factory()->create(['email' => 'punya.a@test.com']);
        User::factory()->create(['email' => 'punya.b@test.com']);

        $this->postJson('/api/user/email', [
            'user_id' => $a->id,
            'email'   => 'punya.b@test.com',
        ])->assertStatus(422);
    }

    public function test_ganti_email_menolak_format_salah(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/user/email', [
            'user_id' => $user->id,
            'email'   => 'bukan-email',
        ])->assertStatus(422);
    }

    public function test_profil_mengembalikan_slot_aktif(): void
    {
        $user = User::factory()->create([
            'notify_slot_1' => true,
            'notify_slot_2' => false,
            'notify_slot_3' => true,
        ]);

        $this->getJson("/api/user?user_id={$user->id}")
             ->assertOk()
             ->assertJsonPath('slots', ['slot-1', 'slot-3']);
    }
}