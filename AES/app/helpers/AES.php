<?php

class AES {

    private static function key() {

        if (empty($_ENV['AES_SECRET_KEY'])) {
            throw new Exception("AES_SECRET_KEY not configured");
        }

        // Ensure 32 bytes key for AES-256
        return hash('sha256', $_ENV['AES_SECRET_KEY'], true);
    }

    /* ================= ENCRYPT ================= */

    public static function encrypt($plainText) {

        if ($plainText === null || $plainText === '') {
            return null;
        }

        $cipher = "AES-256-CBC";

        $ivLength = openssl_cipher_iv_length($cipher);
        $iv = random_bytes($ivLength);

        $encrypted = openssl_encrypt(
            $plainText,
            $cipher,
            self::key(),
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($encrypted === false) {
            throw new Exception("Encryption failed");
        }

        return base64_encode($iv . $encrypted);
    }

    /* ================= DECRYPT ================= */

    public static function decrypt($encryptedData) {

        if (empty($encryptedData)) {
            return null;
        }

        $cipher = "AES-256-CBC";
        $key = self::key();

        $data = base64_decode($encryptedData, true);

        if ($data === false) {
            return null;
        }

        $ivLength = openssl_cipher_iv_length($cipher);

        if (strlen($data) < $ivLength) {
            return null;
        }

        $iv = substr($data, 0, $ivLength);
        $cipherText = substr($data, $ivLength);

        $decrypted = openssl_decrypt(
            $cipherText,
            $cipher,
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );

        return $decrypted === false ? null : $decrypted;
    }
}
