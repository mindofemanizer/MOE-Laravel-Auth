<?php

namespace Moe\Auth\Services\Otp;

interface ChannelInterface
{
    public function send(string $identifier, string $code): bool;
}
