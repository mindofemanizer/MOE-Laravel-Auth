<?php

namespace Moe\Auth\Services\Otp;

use Illuminate\Support\Facades\Http;

class WhatsAppChannel implements ChannelInterface
{
    public function send(string $identifier, string $code): bool
    {
        $provider = config('moe-auth.otp.channels.whatsapp.provider', 'fonnte');
        $apiKey = config('moe-auth.otp.channels.whatsapp.api_key', '');
        $apiUrl = config('moe-auth.otp.channels.whatsapp.api_url', '');

        if (empty($apiKey)) {
            return false;
        }

        $appName = config('app.name', 'Application');
        $expiry = intdiv(config('moe-auth.otp.expiry', 300), 60);

        $template = config('moe-auth.otp.message.whatsapp', '{app_name} verification code: {code}');
        $message = str_replace(
            ['{code}', '{minutes}', '{app_name}'],
            [$code, $expiry, $appName],
            $template
        );

        try {
            return match ($provider) {
                'fonnte' => $this->sendFonnte($identifier, $message, $apiKey, $apiUrl),
                'wablas' => $this->sendWablas($identifier, $message, $apiKey, $apiUrl),
                default => false,
            };
        } catch (\Exception) {
            return false;
        }
    }

    protected function sendFonnte(string $phone, string $message, string $apiKey, string $apiUrl): bool
    {
        $url = $apiUrl ?: 'https://api.fonnte.com/send';

        $response = Http::withHeaders([
            'Authorization' => $apiKey,
            'Content-Type' => 'application/json',
        ])->post($url, [
            'target' => $phone,
            'message' => $message,
        ]);

        $data = $response->json();

        return $data['status'] ?? false;
    }

    protected function sendWablas(string $phone, string $message, string $apiKey, string $apiUrl): bool
    {
        $url = $apiUrl ?: 'https://wablas.com/api/send-message';

        $response = Http::withHeaders([
            'Authorization' => $apiKey,
        ])->post($url, [
            'phone' => $phone,
            'message' => $message,
        ]);

        $data = $response->json();

        return $data['success'] ?? false;
    }
}
