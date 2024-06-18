<?php
session_start();

if (isset($_SESSION['user'])) {
  header("Location: home.php");
  exit;
}

if (isset($_GET['action']) && $_SERVER["REQUEST_METHOD"] === "POST") {

  $msg = null;
  $validReq = isset(
    $_POST['usermail'],
    $_POST['password']
  ) &&
    is_string($_POST['usermail']) &&
    is_string($_POST['password']);

  if (!$validReq) {
    $msg = 'Invalid request!';
    goto show_html;
  }

  $query = "SELECT `id`, `password` FROM `users` ";
  if (filter_var($_POST['usermail'], FILTER_VALIDATE_EMAIL)) {
    $query .= "WHERE `email` = ?";
  } else {
    $query .= "WHERE `username` = ?";
  }

  $pdo =  require __DIR__ . '/db.php';
  $st = $pdo->prepare($query);
  $st->execute([$_POST['usermail']]);
  $st = $st->fetch(\PDO::FETCH_NUM);
  if (!$st) {
    $msg = 'Email or Password is invalid';
    goto show_html;
  }

  if (!password_verify($_POST['password'], $st[1])) {
    $msg = 'Wrong password';
    goto show_html;
  }

  $_SESSION['user'] = $st[0];
  // $_SESSION['login_at'] = time();
  header("Location: home.php");
  exit;
}

show_html:

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | Melchior</title>
  <link rel="stylesheet" href="css/app.css">
</head>

<body>
  <div>

    <h1>Login Form</h1>
    <?php if (isset($msg)) : ?>
      <h4>Error: <?= htmlspecialchars($msg) ?></h4>
    <?php endif; ?>
    <form action="?action=1" method="POST">
      <label>
        Username or Email
        <input type="text" name="usermail" required>
      </label> <br><br>

      <label>
        Password
        <input type="password" name="password" required>
      </label> <br><br>

      <label>
        <input type="submit" name="login" value="Login">
      </label> <br><br>
    </form>
    <samp>Don't have an account? <a href="register.php">Register</a></samp>
  </div>
</body>

</html>