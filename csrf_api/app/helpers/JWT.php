<?php
class JWT {
    private static function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode($data) {
        return base64_decode(strtr($data, '-_', '+/'));
    }

    private static function getSecret() {
        return trim($_ENV['JWT_SECRET']);
    }

    public static function generate($payload) {
        $header = ['alg' => 'HS256', 'typ' => 'JWT'];

        $headerEncoded  = self::base64UrlEncode(json_encode($header));
        $payloadEncoded = self::base64UrlEncode(json_encode($payload));

        $signature = hash_hmac(
            'sha256',
            "$headerEncoded.$payloadEncoded",
            self::getSecret(),
            true
        );

        return "$headerEncoded.$payloadEncoded." . self::base64UrlEncode($signature);
    }

    public static function verify($token) {
        $parts = explode('.', $token);
        if (count($parts) !== 3) return false;

        [$header, $payload, $signature] = $parts;
        // server creates signature again 
        $valid = self::base64UrlEncode(
            hash_hmac('sha256', "$header.$payload", self::getSecret(), true)
        );

        if (!hash_equals($signature, $valid)) return false;

        $data = json_decode(self::base64UrlDecode($payload), true);
        if ($data['exp'] < time()) return false;

        return $data;
    }
}
