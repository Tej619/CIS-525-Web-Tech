<?php
require_once 'db.php';

$errors = $_SESSION['login_errors'] ?? null;
unset($_SESSION['login_errors']);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Login to Manage Registration</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <div class="container">
    <h2>Login to Manage Your Registration</h2>
    <?php if (!empty($errors)) echo '<div class="error">'.$errors.'</div>'; ?>
    <form method="post" action="manage.php">
      <div class="form-row">
        <div class="col">
          <label for="umid">UMID</label>
          <input id="umid" name="umid" type="text" required maxlength="8">
        </div>
        <div class="col">
          <label for="password">Password</label>
          <input id="password" name="password" type="password" required>
        </div>
      </div>
      <div class="form-row">
        <button type="submit">Login</button>
        <div class="col small">Or <a href="register.php">register</a> if you are new.</div>
      </div>
    </form>
  </div>
</body>
</html>
