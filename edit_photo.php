<?php

if (!isset($pdo)) {
  header("Location: profile.php");
  exit;
}

const ALLOWED_EXTS = [
  'jpg', 'jpeg', 'png', 'gif'
];
const MAX_ALLOWED_SIZE = 1024 * 1024 * 4;

function edit_photo(\PDO $connection, string &$err): bool
{
  if (!$_FILES['pfp']) {
    $err = "";
    return false;
  }
  $ext = explode(".", $_FILES['pfp']['name'], 2);
  $ext = $ext[count($ext) - 1]; // equal to end($ext)

  if (!in_array($ext, ALLOWED_EXTS)) {
    $err = sprintf(
      "Extension '%s' isn't allowed [allowed extensions: %s]",
      $ext,
      implode(',', ALLOWED_EXTS)
    );

    return false;
  }

  if ($_FILES['pfp']['size'] > MAX_ALLOWED_SIZE) {
    $err = sprintf("File size is too large, max allowed size is 4Mb (%s)", MAX_ALLOWED_SIZE);
    return false;
  }

  $md5sum = md5_file($_FILES['pfp']['tmp_name']);
  $sha1sum = sha1_file($_FILES['pfp']['tmp_name']);
  $filename = $sha1sum . '_' . $md5sum . '.' . $ext;

  $path = __DIR__ . "/storage/files/{$filename}";
  if (!move_uploaded_file($_FILES['pfp']['tmp_name'], $path)) {
    $err = "Cannot move uploaded file";
    return false;
  }

  $md5sum = hex2bin($md5sum);
  $sha1sum = hex2bin($sha1sum);
  $fileId = null;

  /**
   * ACID
   * @see https://en.wikipedia.org/wiki/ACID
   */
  $connection->beginTransaction();
  $st = $connection->prepare("SELECT `id` FROM `files` WHERE `md5_sum` = ? AND `sha1_sum` = ? LIMIT 1");
  $st->execute([$md5sum, $sha1sum]);
  $fileExist = $st->fetch(\PDO::FETCH_NUM);

  if (!$fileExist) {
    $addNew = $connection->prepare(<<<SQL
      INSERT INTO 
      `files` (`md5_sum`, `sha1_sum`, `size`, `ext`, `description`, `created_at`) 
      VALUES (?, ?, ?, ?, NULL, ?)
    SQL);
    $an = $addNew->execute([
      $md5sum,
      $sha1sum,
      $_FILES['pfp']['size'],
      $ext,
      date('Y-m-d H:i:s')
    ]);

    if (!$an) {
      $err = "Failed to insert the photo to database";
      goto out_rollback;
    }

    $fileId = $connection->lastInsertId();
  } else {
    $fileId = $fileExist[0];
  }

  $st = $connection->prepare("UPDATE `users` SET `photo` = ? WHERE `id` = ? LIMIT 1");
  $st->execute([$fileId, $_SESSION['user']]);
  if (!$st) {
    $err = "Failed to update profile photo";
    goto out_rollback;
  }

  $connection->commit();
  return true;

  out_rollback:
  $connection->rollback();
  return false;
}

$err = "";
if (isset($_POST['submit'])) {
  if(edit_photo($pdo, $err))
    header("Location: profile.php");
    exit;
}

show_html:
?>

<?php if ($err !== "") : ?>
  <samp>
    <?= htmlspecialchars($err, ENT_QUOTES, "UTF-8") ?>
  </samp>
<?php endif; ?>

<form action="" method="post" enctype="multipart/form-data">
  <div>
    <input type="file" name="pfp" id="pfp">
  </div>
  <div>
    <input type="submit" name="submit" value="Upload">
  </div>
</form>
<span onclick="window.location='profile.php'" style="cursor: pointer;">Back</span>