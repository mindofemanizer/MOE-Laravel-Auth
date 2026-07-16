<?php

namespace Moe\Auth\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $code,
        public string $message,
    ) {}

    public function build(): self
    {
        return $this->subject(config('app.name', 'Application') . ' — Verification Code')
            ->view('moe-auth::emails.otp');
    }
}
