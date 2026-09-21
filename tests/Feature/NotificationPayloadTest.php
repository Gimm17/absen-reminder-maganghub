<?php

namespace Tests\Feature;

use App\Models\Checkin;
use App\Models\User;
use App\Services\PushNotificationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationPayloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_payload_punya_judul_dan_isi_berbeda_tiap_slot(): void
    {
        $titles = [];
        foreach (['slot-1', 'slot-2', 'slot-3'] as $slot) {
            $p = PushNotificationService::buildReminderPayload($slot);
            $this->assertNotEmpty($p['title'], "judul {$slot} kosong");
            $this->assertNotEmpty($p['body'], "isi {$slot} kosong");
            $titles[] = $p['title'];
        }

        $this->assertCount(3, array_unique($titles), 'judul tiap slot harus berbeda');
    }

    public function test_payload_tidak_pernah_pakai_opsi_sound(): void
    {
        // Notification API tidak mendukung `sound`. Kalau ada, berarti salah paham.
        foreach (['slot-1', 'slot-2', 'slot-3'] as $slot) {
            $p = PushNotificationService::buildReminderPayload($slot);
            $this->assertArrayNotHasKey('sound', $p, 'opsi sound tidak didukung browser');
        }
    }

    public function test_pola_getar_makin_kuat_tiap_slot(): void
    {
        $v1 = PushNotificationService::buildReminderPayload('slot-1')['vibrate'];
        $v2 = PushNotificationService::buildReminderPayload('slot-2')['vibrate'];
        $v3 = PushNotificationService::buildReminderPayload('slot-3')['vibrate'];

        $this->assertIsArray($v1);
        $this->assertIsArray($v2);
        $this->assertIsArray($v3);

        // Total durasi getar harus naik: pengingat terakhir paling terasa.
        $this->assertGreaterThan(array_sum($v1), array_sum($v2), 'slot-2 harus lebih kuat dari slot-1');
        $this->assertGreaterThan(array_sum($v2), array_sum($v3), 'slot-3 harus paling kuat');
    }

    public function test_slot_terakhir_bertahan_di_layar(): void
    {
        $this->assertTrue(
            PushNotificationService::buildReminderPayload('slot-3')['requireInteraction'],
            'slot terakhir harus requireInteraction supaya tidak hilang sendiri',
        );
        $this->assertFalse(
            PushNotificationService::buildReminderPayload('slot-1')['requireInteraction'],
        );
    }

    public function test_tag_dan_renotify_konsisten(): void
    {
        foreach (['slot-1', 'slot-2', 'slot-3'] as $slot) {
            $p = PushNotificationService::buildReminderPayload($slot);
            $this->assertNotEmpty($p['tag']);
            $this->assertTrue($p['renotify'], 'renotify wajib kalau pakai tag (kalau tidak, notif bisu)');
        }
    }

    public function test_badge_menunjuk_file_yang_benar_benar_ada(): void
    {
        // Bug lama: badge -> /icons/icon-72.png yang tidak pernah dibuat (404).
        foreach (['slot-1', 'slot-2', 'slot-3'] as $slot) {
            $p = PushNotificationService::buildReminderPayload($slot);
            $badge = public_path(ltrim($p['badge'], '/'));
            $icon = public_path(ltrim($p['icon'], '/'));

            $this->assertFileExists($badge, "badge {$p['badge']} tidak ada di public/");
            $this->assertFileExists($icon, "icon {$p['icon']} tidak ada di public/");
        }
    }

    public function test_aksi_checkin_di_kirim_pertama(): void
    {
        $actions = PushNotificationService::buildReminderPayload('slot-2')['data']['actions'];
        $this->assertSame('checkin', $actions[0]['action'], 'aksi utama harus "Sudah Absen"');
        $this->assertNotEmpty($actions[0]['title']);
    }

    public function test_checkin_lewat_device_token_berhasil(): void
    {
        $user = User::factory()->create();
        $this->assertNotEmpty($user->device_token, 'user baru harus dapat device_token');

        $r = $this->postJson('/api/checkin', [
            'device_token' => $user->device_token,
            'source'       => 'notification',
        ]);

        $r->assertStatus(201)->assertJson(['ok' => true, 'created' => true]);
        $this->assertDatabaseHas('checkins', [
            'user_id' => $user->id,
            'date'    => Carbon::now('Asia/Makassar')->toDateString(),
        ]);
    }

    public function test_checkin_device_token_palsu_ditolak(): void
    {
        $this->postJson('/api/checkin', ['device_token' => 'token-palsu-123'])
             ->assertStatus(404);
    }

    public function test_checkin_tanpa_user_id_dan_token_ditolak(): void
    {
        $this->postJson('/api/checkin', [])->assertStatus(422);
    }

    public function test_batal_checkin_lewat_device_token(): void
    {
        $user = User::factory()->create();
        $this->postJson('/api/checkin', ['device_token' => $user->device_token])->assertStatus(201);

        $this->deleteJson('/api/checkin', ['device_token' => $user->device_token])
             ->assertOk()
             ->assertJson(['deleted' => true]);

        $this->assertEquals(0, Checkin::where('user_id', $user->id)->count());
    }
}
