<?php

namespace Moe\Auth\Services\Otp;

use Illuminate\Support\Facades\Http;

class SmsChannel implements ChannelInterface
{
    public function send(string $identifier, string $code): bool
    {
        $provider = config('moe-auth.otp.channels.sms.provider', 'twilio');
        $apiKey = config('moe-auth.otp.channels.sms.api_key', '');
        $apiSecret = config('moe-auth.otp.channels.sms.api_secret', '');
        $from = config('moe-auth.otp.channels.sms.from', '');

        if (empty($apiKey)) {
            return false;
        }

        $appName = config('app.name', 'Application');
        $expiry = intdiv(config('moe-auth.otp.expiry', 300), 60);

        $template = config('moe-auth.otp.message.sms', '{app_name}: {code} is your verification code');
        $message = str_replace(
            ['{code}', '{minutes}', '{app_name}'],
            [$code, $expiry, $appName],
            $template
        );

        try {
            return match ($provider) {
                'twilio' => $this->sendTwilio($identifier, $message, $apiKey, $apiSecret, $from),
                'nexmo' => $this->sendNexmo($identifier, $message, $apiKey, $apiSecret, $from),
                default => false,
            };
        } catch (\Exception) {
            return false;
        }
    }

    protected function sendTwilio(string $to, string $body, string $sid, string $token, string $from): bool
    {
        $url = "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json";

        $response = Http::withBasicAuth($sid, $token)
            ->asForm()
            ->post($url, [
                'To' => $to,
                'From' => $from,
                'Body' => $body,
            ]);

        return $response->successful();
    }

    protected function sendNexmo(string $to, string $body, string $apiKey, string $apiSecret, string $from): bool
    {
        $url = 'https://rest.nexmo.com/sms/json';

        $response = Http::asForm()->post($url, [
            'api_key' => $apiKey,
            'api_secret' => $apiSecret,
            'to' => $to,
            'from' => $from,
            'text' => $body,
        ]);

        $data = $response->json();

        return ($data['messages'][0]['status'] ?? '') === '0';
    }
}
