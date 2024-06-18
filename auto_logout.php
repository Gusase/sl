<?php
if (isset($_SESSION['login_at'])) {
  if ($_SESSION['login_at'] + 10 > time()) {
?>
    <script>
      alert("Session expired!");
      window.location = "logout.php"
    </script>
<?php
    // header("Location: logout.php");
    exit;
  }
}
