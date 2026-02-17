<?php

class AES {

    private static function key() {

        if (empty($_ENV['AES_SECRET'])) {
            throw new Exception("AES_SECRET not defined in .env");
        }

        return hash('sha256', $_ENV['AES_SECRET'], true);
    }

    public static function encrypt($plaintext) {

        $cipher = "AES-256-CBC";

        $ivLength = openssl_cipher_iv_length($cipher);
        $iv = random_bytes($ivLength);

        $encrypted = openssl_encrypt(
            $plaintext,
            $cipher,
            self::key(),
            OPENSSL_RAW_DATA,
            $iv
        );

        return base64_encode($iv . $encrypted);
    }

    public static function decrypt($encryptedData) {

        $cipher = "AES-256-CBC";

        $data = base64_decode($encryptedData);

        $ivLength = openssl_cipher_iv_length($cipher);

        if (strlen($data) < $ivLength) {
            return false;
        }

        $iv = substr($data, 0, $ivLength);
        $encrypted = substr($data, $ivLength);

        return openssl_decrypt(
            $encrypted,
            $cipher,
            self::key(),
            OPENSSL_RAW_DATA,
            $iv
        );
    }
}
