<?php
function validateUsername($username){
    if(empty($username)){
        return "Username is required<br>";
    }
    if (strlen($username) < 4){
        return "Username must be atleast 4 characters";
    }
    return "";
}
function validateEmail($email) {
    if (empty($email)) {
        return "Email is required<br>";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "Invalid email format";
    }
    return "";
}

function validatePassword($password) {
    if (empty($password)) {
        return "Password is required<br>";
    }
    if (strlen($password) < 8) {
        return "Password must be at least 8 characters";
    }
    return "";
}
?>