<?php

if (!isset($pdo)) {
  header("Location: profile.php");
  exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

  $msg = null;
  $validReq = isset(
    $_POST['old_password'],
    $_POST['new_password'],
    $_POST['cnew_password'],
  ) &&
    is_string($_POST['old_password']) &&
    is_string($_POST['new_password']) &&
    is_string($_POST['cnew_password']);

  if (!$validReq) {
    $msg = 'Invalid request!';
    goto show_html;
  }

  $st = $pdo->prepare("SELECT `password` FROM `users` WHERE `id` = ?");
  $st->execute([$_SESSION['user']]);
  $st = $st->fetch(\PDO::FETCH_NUM);
  if (!$st) {
    $msg = "Invalid user_id '__')";
    goto show_html;
  }

  if (!password_verify($_POST['old_password'], $st[0])) {
    $msg = 'Wrong password';
    goto show_html;
  }

  $pwLength = strlen($_POST['new_password']);
  if ($pwLength < 6) {
    $msg = 'Password must be at least 6 characters';
    goto show_html;
  }

  if ($pwLength > 100) {
    $msg = 'Fr?!';
    goto show_html;
  }

  if ($_POST['new_password'] !== $_POST['cnew_password']) {
    $msg = 'Password must be the same with Retype Password';
    goto show_html;
  }

  /**
   * Update password
   */
  $st  = $pdo->prepare(
    <<<SQL
    UPDATE `users` SET
      `password` = ?, `updated_at` = ?
    WHERE `id` = ?
  SQL
  );

  $st->execute([
    password_hash($_POST['new_password'], PASSWORD_BCRYPT),
    date('Y-m-d H:i:s'),
    $_SESSION['user']
  ]);


?>
  <script>
    alert('Your password has successfully been changed!');
    window.location = 'profile.php';
  </script>
<?php
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
    Old Password
    <input type="password" name="old_password" required>
  </label> <br><br>

  <label>
    New Password
    <input type="password" name="new_password" required>
  </label> <br><br>

  <label>
    Confirm New Password
    <input type="password" name="cnew_password" required>
  </label> <br><br>

  <label>
    <input type="submit" name="save" value="Save">
  </label>
</form>
<span onclick="window.location='profile.php'" style="cursor: pointer;">Back</span>