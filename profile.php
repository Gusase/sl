<?php
session_start();

if (!isset($_SESSION['user'])) {
  header("Location: login.php");
  exit;
}

$pdo =  require __DIR__ . '/db.php';
$st = $pdo->prepare("SELECT `first_name`, `last_name`, `username`, `email` FROM `users` WHERE `id` = ?");
$st->execute([$_SESSION['user']]);
$profile = $st->fetch(\PDO::FETCH_ASSOC);

$fullname = $profile['first_name'];
if (!is_null($profile['last_name'])) {
  $fullname .= " " . $profile['last_name'];
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($fullname) ?>'s Profile</title>
  <link rel="stylesheet" href="css/app.css">
</head>

<body>
  <?php if (isset($_GET['edit'])) : ?>
    <?php require __DIR__ . '/edit.php' ?>
  <?php elseif (isset($_GET['change_pass'])) : ?>
    <?php require __DIR__ . '/edit_password.php' ?>
  <?php else : ?>
    <h2>Logged in as <?= htmlspecialchars($fullname . "(@" . $profile['username'] . ')') ?></h2>
    <a href="?edit=true">Edit Profile</a> <br>
    <a href="?change_pass=true">Change Password</a> <br>
  <?php endif; ?>
  <span onclick="history.go(-1)" style="cursor: pointer;">Back</span>
</body>

</html>