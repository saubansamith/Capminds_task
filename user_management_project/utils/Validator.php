<?php

namespace utils;

class Validator
{
    public function validateUsername($username)
    {
        return strlen($username) >= 5 ? "valid" : "Invalid";
    }
    public function validateEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? "Valid" : "Invalid";
    }

    public function validatePassword($password)
    {
        return strlen($password) >= 8 ? "Strong" : "Weak";
    }
}

?>