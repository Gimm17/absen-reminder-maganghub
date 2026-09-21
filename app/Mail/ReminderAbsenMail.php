<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReminderAbsenMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $slot,
        public string $judul,
        public string $pesan,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->judul,
            from: config('mail.from.address'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reminder-absen',
            with: [
                'nama'         => $this->user->name,
                'judul'        => $this->judul,
                'pesan'        => $this->pesan,
                'slot'         => $this->slot,
                'dashboardUrl' => config('app.maganghub_dashboard_url'),
                'loginUrl'     => config('app.maganghub_login_url'),
                'isLastSlot'   => $this->slot === 'slot-3',
                'appUrl'       => config('app.url'),
                'deadline'     => 'tengah malam (00.00 WITA)',
            ],
        );
    }
}