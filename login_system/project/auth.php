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
$users = [
    [
        "username" => "admin",
        "email" => "admin@example.com",
        "password" => "Admin@123",
        "theme" => "dark"  
    ],
    [
        "username" => "user1",
        "email" => "user1@gmail.com",
        "password" => "User@123",
        "theme" => "warm"
    ],
    [
        "username" => "user2",
        "email" => "user2@gmail.com",
        "password" => "User@456",
        "theme" => "light"
    ]
];
$foundUser = null;

foreach ($users as $user) {
    if (
        $user['username'] == $username &&
        $user['email'] == $email &&
        $user['password'] == $password
    ) {
        $foundUser = $user;
        break;
    }
}

if ($foundUser) {

    $theme = $foundUser['theme'];

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


