<?php

namespace Ycs77\LaravelRecoverSession\Support;

class Base64Url
{
    /**
     * Encoding base64 data for URL.
     */
    public static function encode(string $payload): string
    {
        return rtrim(strtr($payload, '+/', '-_'), '=');
    }

    /**
     * Decoding base64 data from URL.
     */
    public static function decode(string $payload): string
    {
        return str_pad(strtr($payload, '-_', '+/'), ceil(strlen($payload) / 4) * 4, '=', STR_PAD_RIGHT);
    }
}
