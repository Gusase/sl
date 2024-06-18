<?php

session_start();

if (!isset($_SESSION['user'])) {
  header("Location: login.php");
  exit;
}

// require __DIR__ . '/auto_logout.php';

$pdo = require __DIR__ . '/db.php';
$st = $pdo->prepare("SELECT `first_name` FROM `users` WHERE `id` = ?");
$st->execute([$_SESSION['user']]);
$st = $st->fetch(\PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Melchior</title>
  <link rel="stylesheet" href="css/app.css">
</head>

<body>
  <h1>Welcome <?= htmlspecialchars($st['first_name']) ?>!</h1>
  <a href="profile.php">Your Profile</a> <br>
  <a href="logout.php">Logout</a>
</body>

</html>