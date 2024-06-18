<?php
session_start();

if (!isset($_SESSION['user'])) {
  header("Location: login.php");
  exit;
}

const PHOTO_PATH = "storage/files/";
$pdo =  require __DIR__ . '/db.php';
$st = $pdo->prepare("SELECT `first_name`, `last_name`, `username`, `email`, `photo` 
                    FROM `users` 
                    WHERE `id` = ?");
$st->execute([$_SESSION['user']]);
$profile = $st->fetch(\PDO::FETCH_ASSOC);

if (!$profile) {
  // Current (user) profile not fuond '__')
  require __DIR__ . '/logout.php';
  exit;
}

$fullname = $profile['first_name'];
if (!is_null($profile['last_name'])) {
  $fullname .= " " . $profile['last_name'];
}

$photo = null;
if (!is_null($profile['photo'])) {
  $st = $pdo->prepare("SELECT LOWER(HEX(`md5_sum`)), LOWER(HEX(`sha1_sum`)), `ext` FROM `files` WHERE `id` = ? LIMIT 1");
  $st->execute([$profile['photo']]);
  $tmp = $st->fetch(\PDO::FETCH_NUM);
  if ($tmp) {
    $photo = PHOTO_PATH . '/' . $tmp[1] . "_" . $tmp[0] . "." . $tmp[2];
  }
}

if (is_null($photo)) {
  $photo = 'pppolos.png';
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
    <?php if (isset($_GET['action'])) : ?>
      <?php
      switch ($_GET['action']) {
        case 'edit_photo':
          require __DIR__ . '/edit_photo.php';
          break;
        default:
          break;
      }
      ?>
    <?php else : ?>
      <img src="<?= htmlspecialchars($photo, ENT_QUOTES, "UTF-8") ?>" alt="<?= htmlspecialchars($profile['username']) ?>'s pfp" style="border-radius: 100vh;max-width: 100%; width: 100px; object-fit: cover;" sizes="" srcset=""> <br>
      <a href="?action=edit_photo">Edit Photo</a> <br>
    <?php endif; ?>
    <h2>Logged in as <?= htmlspecialchars($fullname . "(@" . $profile['username'] . ')') ?></h2>
    <a href="?edit=true">Edit Profile</a> <br>
    <a href="?change_pass=true">Change Password</a> <br>
    <span onclick="window.location='home.php'" style="cursor: pointer;">Home</span>
  <?php endif; ?>
</body>

</html>