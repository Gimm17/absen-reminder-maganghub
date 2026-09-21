<?php

namespace Tests\Feature;

use App\Mail\ReminderAbsenMail;
use App\Models\Checkin;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ReminderEmailTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        // Aktifkan jalur email untuk test (bukan log/array).
        config()->set('mail.default', 'smtp');
        config()->set('mail.from.address', 'absenhub@pinnhost.my.id');
    }

    public function test_email_terkirim_ke_user_yang_belum_absen(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'magang@test.com',
            'is_active' => true,
            'notify_slot_1' => true,
        ]);

        $this->artisan('reminders:send slot-1 --force')->assertSuccessful();

        Mail::assertSent(ReminderAbsenMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email) && $mail->slot === 'slot-1';
        });
    }

    public function test_email_tidak_terkirim_kalau_sudah_absen(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'sudahabsen@test.com',
            'is_active' => true,
            'notify_slot_1' => true,
        ]);

        Checkin::create([
            'user_id' => $user->id,
            'date' => Carbon::now('Asia/Makassar')->toDateString(),
            'reported_at' => now(),
            'source' => 'button',
        ]);

        $this->artisan('reminders:send slot-1 --force')->assertSuccessful();

        Mail::assertNotSent(ReminderAbsenMail::class);
    }

    public function test_email_tidak_dikirim_kalau_mailer_log(): void
    {
        Mail::fake();
        config()->set('mail.default', 'log');

        User::factory()->create([
            'email' => 'magang2@test.com',
            'is_active' => true,
            'notify_slot_1' => true,
        ]);

        $this->artisan('reminders:send slot-1 --force')->assertSuccessful();

        Mail::assertNotSent(ReminderAbsenMail::class);
    }

    public function test_admin_tidak_dapat_email(): void
    {
        Mail::fake();

        User::factory()->create([
            'email' => 'admin@test.com',
            'role' => 'admin',
            'is_active' => true,
            'notify_slot_1' => true,
        ]);

        $this->artisan('reminders:send slot-1 --force')->assertSuccessful();

        Mail::assertNotSent(ReminderAbsenMail::class);
    }
}