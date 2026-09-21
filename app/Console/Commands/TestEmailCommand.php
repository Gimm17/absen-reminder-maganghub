<?php

namespace App\Console\Commands;

use App\Mail\ReminderAbsenMail;
use App\Models\User;
use App\Services\PushNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmailCommand extends Command
{
    protected $signature = 'reminders:test-email {email : Alamat tujuan} {--slot=slot-1 : Slot yg dipakai untuk isi pesan} {--name=Test : Nama penerima}';

    protected $description = 'Kirim 1 email reminder percobaan untuk verifikasi konfigurasi SMTP.';

    public function handle(): int
    {
        $email = $this->argument('email');
        $slot = $this->option('slot');
        $name = $this->option('name');

        $this->info("Mailer   : " . config('mail.default'));
        $this->info("Host     : " . config('mail.mailers.smtp.host') . ':' . config('mail.mailers.smtp.port'));
        $this->info("From     : " . config('mail.from.address'));
        $this->info("Tujuan   : {$email}");
        $this->info("Slot     : {$slot}");
        $this->newLine();

        $payload = PushNotificationService::buildReminderPayload($slot);

        // User dummy — hanya untuk mengisi view, tidak disimpan ke DB.
        $user = new User(['name' => $name, 'email' => $email]);

        try {
            Mail::to($email)->send(new ReminderAbsenMail(
                $user,
                $slot,
                $payload['title'],
                $payload['body'],
            ));
        } catch (\Throwable $e) {
            $this->error('GAGAL kirim: ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->info('Email terkirim ke SMTP. Cek inbox (dan folder spam) penerima.');
        return self::SUCCESS;
    }
}