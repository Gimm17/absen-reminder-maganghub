<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Minishlink\WebPush\VAPID;

class GenerateVapidKeys extends Command
{
    protected $signature = 'webpush:vapid {--show : print keys only, do not touch .env}';

    protected $description = 'Generate VAPID keypair untuk Web Push dan tulis ke .env';

    public function handle(): int
    {
        $keys = VAPID::createVapidKeys();

        if ($this->option('show')) {
            $this->line('Public:  ' . $keys['publicKey']);
            $this->line('Private: ' . $keys['privateKey']);
            return self::SUCCESS;
        }

        $envPath = base_path('.env');
        if (! file_exists($envPath)) {
            $this->error('.env tidak ditemukan. Jalankan: cp .env.example .env');
            return self::FAILURE;
        }

        $env = file_get_contents($envPath);
        $env = preg_replace('/^VAPID_PUBLIC_KEY=.*$/m', 'VAPID_PUBLIC_KEY=' . $keys['publicKey'], $env, 1, $pCount);
        $env = preg_replace('/^VAPID_PRIVATE_KEY=.*$/m', 'VAPID_PRIVATE_KEY=' . $keys['privateKey'], $env, 1, $kCount);

        if ($pCount === 0) $env .= "\nVAPID_PUBLIC_KEY=" . $keys['publicKey'] . "\n";
        if ($kCount === 0) $env .= "VAPID_PRIVATE_KEY=" . $keys['privateKey'] . "\n";

        // Tambah VAPID_SUBJECT kalau belum ada.
        if (! str_contains($env, 'VAPID_SUBJECT=')) {
            $env .= "VAPID_SUBJECT=\"mailto:admin@localhost\"\n";
        }

        file_put_contents($envPath, $env);

        $this->info('VAPID keys berhasil ditulis ke .env');
        $this->line('Public:  ' . $keys['publicKey']);
        $this->line('Private: (hidden — simpan di server)');
        $this->warn('Pastikan VAPID_SUBJECT berisi mailto valid atau https URL agar Apple push tidak reject.');
        return self::SUCCESS;
    }
}