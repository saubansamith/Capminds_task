<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$theme = $_SESSION['theme'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body.light { background:#f8f9fa; }
        body.dark { background:#212529; color:white; }
        body.warm { background:#fff3cd; }
    </style>
</head>

<body class="<?php echo $theme; ?>">

<div class="container mt-5">
    <h2>Welcome, <?php echo $_SESSION['username']; ?></h2>
    <p>Email: <?php echo $_SESSION['email']; ?></p>
    <p>Theme: <?php echo $_SESSION['theme']; ?></p>

    <a href="logout.php" class="btn btn-danger">Logout</a>
</div>

</body>
</html>
