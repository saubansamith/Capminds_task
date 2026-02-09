<?php
class JWT {

    private static function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode($data) {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode(strtr($data, '-_', '+/'));
    }
    

    private static function getSecret() {
        return trim($_ENV['JWT_SECRET']);
    }

    public static function generate($payload) {
        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
    
        $time = time();
        $payload['iat'] = $time;
        $payload['exp'] = $time + (int)$_ENV['JWT_EXPIRY'];
    
        $headerEncoded  = self::base64UrlEncode(json_encode($header));
        $payloadEncoded = self::base64UrlEncode(json_encode($payload));
    
        $secret = self::getSecret();
    
        $signature = hash_hmac(
            'sha256',
            "$headerEncoded.$payloadEncoded",
            $secret,
            true
        );
    
        $signatureEncoded = self::base64UrlEncode($signature);
    
        return "$headerEncoded.$payloadEncoded.$signatureEncoded";
    }
    

    public static function verify($token) {
        $parts = explode('.', $token);
        if (count($parts) !== 3) return false;

        list($headerEncoded, $payloadEncoded, $signatureEncoded) = $parts;

        $secret = self::getSecret();

        $validSignature = self::base64UrlEncode(
            hash_hmac('sha256', "$headerEncoded.$payloadEncoded", $secret, true)
        );

        if (!hash_equals($signatureEncoded, $validSignature)) {
            return false;
        }

        $payload = json_decode(self::base64UrlDecode($payloadEncoded), true);

        if ($payload['exp'] < time()) {
            return false;
        }

        return $payload;
    }
}
