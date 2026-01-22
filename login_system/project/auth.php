<?php
session_start();
require 'includes/validation.php';
$email = $_POST['email'];
$username = $_POST['username'];
$password = $_POST['password'];
$remember = isset($_POST['remember']);

// Validation
$error = validateEmail($email);
$error .= validateUsername($username);
$error .= validatePassword($password);

if ($error != "") {
    $_SESSION['error'] = $error;
    header("Location: login.php");
    exit();
}

// Dummy Authentication
if ($username == "admin" && $email == "admin@example.com" && $password == "Admin@123") {

    // Theme Logic
    if ($username == "user1") {
        $theme = "dark";
    } elseif ($username == "user2") {
        $theme = "warm";
    } else {
        $theme = "light";
    }

    // Sessions
    $_SESSION['username'] = $username;
    $_SESSION['email'] = $email;
    $_SESSION['theme'] = $theme;

    // Cookies
    if ($remember) {
        setcookie("remember_username", $username, time()+60);
        setcookie("user_theme", $theme, time()+60);
    } else {
        setcookie("remember_username", "", time()-3600);
    }

    header("Location: dashboard.php");
    exit();

} else {
    $_SESSION['error'] = "Invalid login credentials";
    header("Location: login.php");
    exit();
}
?>
