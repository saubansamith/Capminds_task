<?php
session_start();

$username = "";
if(isset($_COOKIE['remember_username'])){
    $username = $_COOKIE['remember_username'];
}

$theme = "light";
if(isset($_COOKIE['remember_theme'])){
    $theme = $_COOKIE['remember_theme'];
}
$error = "";
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body.light { background:#f8f9fa; }
        body.dark { background:#212529; color:white; }
        body.warm { background:#fff3cd; }
    </style>
</head>

<body class="<?php echo $theme; ?>">

<div class="container mt-5">
    <div class="card p-4 mx-auto" style="max-width:400px;">
        <h3 class="text-center">Login</h3>

        <?php if($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="auth.php" method="POST">
            <input type="text" name="email" class="form-control mb-3" placeholder="Email">
            <input type="text" name="username" class="form-control mb-3" placeholder="Username" value="<?php echo $username; ?>">
            <input type="password" name="password" class="form-control mb-3" placeholder="Password">

            <div class="form-check mb-3">
                <input type="checkbox" name="remember" class="form-check-input">
                <label class="form-check-label">Remember Me</label>
            </div>

            <button class="btn btn-primary w-100">Login</button>
        </form>
    </div>
</div>

</body>
</html>
?>