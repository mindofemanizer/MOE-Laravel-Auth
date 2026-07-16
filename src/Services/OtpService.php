<?php

namespace Moe\Auth\Services;

use Moe\Auth\Models\OtpCode;
use Moe\Auth\Services\Otp\EmailChannel;
use Moe\Auth\Services\Otp\WhatsAppChannel;
use Moe\Auth\Services\Otp\SmsChannel;

class OtpService
{
    protected array $channels = [];

    public function __construct()
    {
        $this->registerChannels();
    }

    protected function registerChannels(): void
    {
        $config = config('moe-auth.otp.channels', []);

        if (($config['email']['enabled'] ?? false)) {
            $this->channels['email'] = app(EmailChannel::class);
        }

        if (($config['whatsapp']['enabled'] ?? false)) {
            $this->channels['whatsapp'] = app(WhatsAppChannel::class);
        }

        if (($config['sms']['enabled'] ?? false)) {
            $this->channels['sms'] = app(SmsChannel::class);
        }
    }

    public function getActiveChannels(): array
    {
        return array_keys($this->channels);
    }

    public function hasActiveChannels(): bool
    {
        return count($this->channels) > 0;
    }

    public function generate(string $identifier, ?string $type = null): string
    {
        $length = config('moe-auth.otp.length', 6);
        $code = str_pad((string) random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);

        $expiry = config('moe-auth.otp.expiry', 300);

        OtpCode::create([
            'identifier' => $identifier,
            'code' => $code,
            'type' => $type,
            'expires_at' => now()->addSeconds($expiry),
        ]);

        return $code;
    }

    public function verify(string $identifier, string $code, ?string $type = null): bool
    {
        $otp = OtpCode::where('identifier', $identifier)
            ->where('code', $code)
            ->where('type', $type)
            ->where('expires_at', '>', now())
            ->where('used', false)
            ->latest()
            ->first();

        if (! $otp) {
            return false;
        }

        $otp->update(['used' => true]);

        return true;
    }

    public function send(string $identifier, string $code, ?string $channel = null): bool
    {
        $channels = $channel ? [$channel] : array_keys($this->channels);

        $sent = false;

        foreach ($channels as $channelName) {
            if (isset($this->channels[$channelName])) {
                $result = $this->channels[$channelName]->send($identifier, $code);
                if ($result) {
                    $sent = true;
                }
            }
        }

        return $sent;
    }

    public function generateAndSend(string $identifier, ?string $type = null, ?string $channel = null): bool
    {
        $code = $this->generate($identifier, $type);

        return $this->send($identifier, $code, $channel);
    }

    public function cleanup(): int
    {
        return OtpCode::where('expires_at', '<', now())->delete();
    }
}
