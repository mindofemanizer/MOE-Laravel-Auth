<?php

namespace Moe\Auth\Services\Otp;

use Illuminate\Support\Facades\Mail;
use Moe\Auth\Mail\OtpMail;

class EmailChannel implements ChannelInterface
{
    public function send(string $identifier, string $code): bool
    {
        $appName = config('app.name', 'Application');
        $expiry = intdiv(config('moe-auth.otp.expiry', 300), 60);

        $template = config('moe-auth.otp.message.email', 'Your verification code is: {code}');
        $message = str_replace(
            ['{code}', '{minutes}', '{app_name}'],
            [$code, $expiry, $appName],
            $template
        );

        try {
            Mail::to($identifier)->send(new OtpMail($code, $message));

            return true;
        } catch (\Exception) {
            return false;
        }
    }
}
