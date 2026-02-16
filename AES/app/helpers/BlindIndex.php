<?php

class BlindIndex {

    public static function emailHash($email) {

        $normalized = strtolower(trim($email));

        return hash_hmac(
            'sha256',
            $normalized,
            $_ENV['HASH_SECRET']
        );
    }

    public static function phoneHash($phone)
{
    $normalized = preg_replace('/[^0-9]/', '', $phone);

    return hash_hmac(
        'sha256',
        $normalized,
        $_ENV['HASH_SECRET']
    );
}

}
