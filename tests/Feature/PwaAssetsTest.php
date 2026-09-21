<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Menjaga aset PWA tetap konsisten.
 *
 * Latar belakang: badge notifikasi pernah menunjuk /icons/icon-72.png
 * yang tidak pernah dibuat -> 404 diam-diam, ikon status bar Android
 * gagal dimuat tanpa error yang terlihat.
 */
class PwaAssetsTest extends TestCase
{
    private function manifest(): array
    {
        $path = public_path('manifest.webmanifest');
        $this->assertFileExists($path, 'manifest.webmanifest tidak ada');

        $json = json_decode(file_get_contents($path), true);
        $this->assertIsArray($json, 'manifest.webmanifest bukan JSON valid');

        return $json;
    }

    public function test_manifest_valid_json_dan_punya_field_wajib(): void
    {
        $m = $this->manifest();

        foreach (['name', 'short_name', 'start_url', 'display', 'icons'] as $field) {
            $this->assertArrayHasKey($field, $m, "manifest kurang field: {$field}");
        }
        $this->assertSame('standalone', $m['display']);
        $this->assertNotEmpty($m['icons']);
    }

    public function test_semua_icon_di_manifest_benar_benar_ada(): void
    {
        $m = $this->manifest();

        foreach ($m['icons'] as $icon) {
            $path = public_path(ltrim($icon['src'], '/'));
            $this->assertFileExists($path, "icon manifest {$icon['src']} tidak ditemukan");
            $this->assertSame('image/png', $icon['type']);
            $this->assertNotEmpty($icon['sizes']);
            $this->assertNotEmpty($icon['purpose'], 'purpose wajib eksplisit (any/maskable)');
        }
    }

    public function test_ada_icon_maskable(): void
    {
        $m = $this->manifest();
        $purposes = array_column($m['icons'], 'purpose');

        $this->assertContains('maskable', $purposes, 'Android butuh minimal 1 icon maskable');
    }

    public function test_shortcut_icons_ada(): void
    {
        $m = $this->manifest();

        foreach ($m['shortcuts'] ?? [] as $shortcut) {
            foreach ($shortcut['icons'] ?? [] as $icon) {
                $path = public_path(ltrim($icon['src'], '/'));
                $this->assertFileExists($path, "icon shortcut {$icon['src']} tidak ditemukan");
            }
        }
    }

    public function test_icon_di_payload_push_ada_di_public(): void
    {
        // Ikon notifikasi (icon/badge) harus benar-benar ada — kalau tidak,
        // notifikasi tampil tanpa ikon dan tidak ada error yg terlihat.
        foreach (['slot-1', 'slot-2', 'slot-3'] as $slot) {
            $p = \App\Services\PushNotificationService::buildReminderPayload($slot);

            $this->assertFileExists(public_path(ltrim($p['icon'], '/')), "icon {$slot} hilang");
            $this->assertFileExists(public_path(ltrim($p['badge'], '/')), "badge {$slot} hilang");
        }
    }

    public function test_app_blade_menangkap_beforeinstallprompt(): void
    {
        // Tanpa tangkapan dini ini, event hilang sebelum Vue mount
        // dan popup install tidak akan pernah muncul.
        $blade = file_get_contents(resource_path('views/app.blade.php'));

        $this->assertStringContainsString('beforeinstallprompt', $blade);
        $this->assertStringContainsString('__pwaInstallEvent', $blade);
        $this->assertStringContainsString('apple-mobile-web-app-capable', $blade, 'iOS butuh meta ini');
    }

    public function test_service_worker_mendaftarkan_push_dan_click(): void
    {
        $sw = file_get_contents(public_path('sw.js'));

        $this->assertStringContainsString("addEventListener('push'", $sw);
        $this->assertStringContainsString("addEventListener('notificationclick'", $sw);
        $this->assertStringContainsString('showNotification', $sw);

        // Suara kustom tidak didukung Notification API. Pastikan tidak ada
        // opsi `sound:` yang benar-benar di-assign (penyebutan di komentar
        // peringatan tidak dihitung).
        $tanpaKomentar = preg_replace('#^\s*(/\*|\*|//).*$#m', '', $sw);
        $this->assertStringNotContainsString(
            'sound:',
            $tanpaKomentar,
            'opsi sound tidak didukung browser — jangan ditambahkan',
        );
    }
}
