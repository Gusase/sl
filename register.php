<?php
session_start();

if (isset($_SESSION['user'])) {
  header("Location: home.php");
  exit;
}

if (isset($_GET['action']) && $_SERVER["REQUEST_METHOD"] === "POST") {

  $msg = null;
  $validReq = isset(
    $_POST['first_name'],
    $_POST['last_name'],
    $_POST['username'],
    $_POST['email'],
    $_POST['password'],
    $_POST['cpassword'],
  ) && is_string($_POST['first_name']) &&
    is_string($_POST['last_name']) &&
    is_string($_POST['username']) &&
    is_string($_POST['email']) &&
    is_string($_POST['password']) &&
    is_string($_POST['cpassword']);

  if (!$validReq) {
    $msg = 'Invalid request!';
    goto show_html;
  }

  $c = strlen($_POST['username']);
  if ($c < 3) {
    $msg = 'Username must be at least 3 characters';
    goto show_html;
  }

  if ($c > 64) {
    $msg = 'Username must not be at more than 64 characters';
    goto show_html;
  }

  $pattern = "/^[a-zA-Z][a-zA-Z0-9\_]{3.63}$/";
  if (preg_match($pattern, $_POST['username'])) {
    $msg = 'Username must match the regex pattern: {$pattern}';
    goto show_html;
  }

  if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    $msg = 'Invalid email format';
    goto show_html;
  }

  $pwLength = strlen($_POST['password']);
  if ($pwLength < 6) {
    $msg = 'Password must be at least 6 characters';
    goto show_html;
  }

  if ($pwLength > 100) {
    $msg = 'Fr?!';
    goto show_html;
  }

  if ($_POST['password'] !== $_POST['cpassword']) {
    $msg = 'Password must be the same with Retype Password';
    goto show_html;
  }

  $pdo = require __DIR__ . '/db.php';

  $username = $_POST['username'];
  $st = $pdo->prepare("SELECT `id` FROM `users` WHERE `username` = ?");
  $st->execute([$username]);
  if ($st->fetch(\PDO::FETCH_NUM)) {
    $msg = "Username '{$username}' has already been used, please use another username";
    goto show_html;
  }

  $email = $_POST['email'];
  $st = $pdo->prepare("SELECT `id` FROM `users` WHERE `email` = ?");
  $st->execute([$email]);
  if ($st->fetch(\PDO::FETCH_NUM)) {
    $msg = "Email '{$email}' has already been used, please use another address";
    goto show_html;
  }

  /**
   * Add new user
   */
  $st  = $pdo->prepare(
    <<<SQL
    INSERT INTO `users` (`first_name`, `last_name`, `username`, `email`, `password`, `created_at`) 
    VALUES (?,?,?,?,?,?);
  SQL
  );

  $success = $st->execute([
    $_POST['first_name'],
    $_POST['last_name'],
    $_POST['username'],
    $_POST['email'],
    password_hash($_POST['password'], PASSWORD_BCRYPT),
    date('Y-m-d H:i:s')
  ]);

  if ($success) {
    header("Location: login.php");
    exit;
  }
}

show_html:

?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register | Melchior</title>
  <link rel="stylesheet" href="/css/app.css">
</head>

<body>
  <div>

    <h1>Register Form</h1>
    <?php if (isset($msg)) : ?>
      <h4>Error: <?= htmlspecialchars($msg) ?></h4>
    <?php endif; ?>
    <form action="?action=1" method="POST">

      <label>
        First Name
        <input type="text" name="first_name" required>
      </label> <br><br>

      <label>
        Last Name
        <input type="text" name="last_name" required>
      </label> <br><br>

      <label>
        Username
        <input type="text" name="username" required>
      </label> <br><br>

      <label>
        Email
        <input type="email" name="email" required>
      </label> <br><br>

      <label>
        Password
        <input type="password" name="password" required>
      </label> <br><br>

      <label>
        Retype Password
        <input type="password" name="cpassword" required>
      </label> <br><br>

      <label>
        <input type="submit" name="register" value="Join">
      </label> <br><br>
    </form>
    <samp>Already have an account? <a href="login.php">Login</a></samp>
  </div>
</body>

</html>