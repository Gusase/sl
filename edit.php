<?php

if (!isset($pdo)) {
  header("Location: profile.php");
  exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
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

  // $em = explode("@", $_POST['email']);
  // # count($em) - 1 length $em minus by 1 | $em[x]
  // $em = $em[count($em) - 1];
  // if (strtolower($em) !== "gmail.com") {
  //   $msg = 'Email must be using gmail.com address';
  //   goto show_html;
  // }

  if (strtolower($_POST['username']) !== $profile['username']) {
    $st = $pdo->prepare("SELECT `id` FROM `users` WHERE `username` = ?");
    $st->execute([$_POST['username']]);
    if ($st->fetch(\PDO::FETCH_NUM)) {
      $msg = "Username \'{$_POST['username']}\' has already been used, please use another username";
      goto show_html;
    }
  }

  if (strtolower($_POST['email']) !== $profile['email']) {
    $st = $pdo->prepare("SELECT `id` FROM `users` WHERE `email` = ?");
    $st->execute([$_POST['email']]);
    if ($st->fetch(\PDO::FETCH_NUM)) {
      $msg = "Email \'{$_POST['email']}\' has already been used, please use another address";
      goto show_html;
    }
  }

  /**
   * Update current user
   */
  $st  = $pdo->prepare(
    <<<SQL
    UPDATE `users` SET
    `first_name` = ?, `last_name` = ?, `username` = ?, 
    `email` = ?, `updated_at` = ?
    WHERE `id` = ?
  SQL
  );

  $st->execute([
    $_POST['first_name'],
    $_POST['last_name'],
    $_POST['username'],
    $_POST['email'],
    date('Y-m-d H:i:s'),
    $_SESSION['user']
  ]);

  header("Location: profile.php");
  exit;
}

show_html:

?>

<?php if (isset($msg)) : ?>
  <script>
    alert("<?= $msg; ?>")
  </script>
<?php endif; ?>

<form action="" method="POST">

  <label>
    First Name
    <input type="text" name="first_name" required value="<?= $profile['first_name'] ?>">
  </label> <br><br>

  <label>
    Last Name
    <input type="text" name="last_name" required value="<?= $profile['last_name'] ?>">
  </label> <br><br>

  <label>
    Username
    <input type="text" name="username" required value="<?= $profile['username'] ?>">
  </label> <br><br>

  <label>
    Email
    <input type="text" name="email" required value="<?= $profile['email'] ?>">
  </label> <br><br>

  <label>
    <input type="submit" name="save" value="Save">
  </label>
</form>
<span onclick="window.location='profile.php'" style="cursor: pointer;">Back</span>