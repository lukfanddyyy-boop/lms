<?php
require_once 'session.php';
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = $_SESSION['login_error'] ?? null;
unset($_SESSION['login_error']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Masuk — LMS Modul Ajar</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body class="auth-page">

<div class="card auth-card">
  <div class="card-header">🔐 Masuk ke Modul Ajar</div>
  <div class="card-body">
    <?php if ($error): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form action="proses_login.php" method="POST">
      <?= csrfField() ?>
      <div class="form-group">
        <label for="username">Username</label>
        <input type="text" name="username" id="username" class="form-control" required autofocus>
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" name="password" id="password" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-primary btn-lg" style="width:100%;">Masuk</button>
    </form>

    <p class="text-center mt-2">
      Belum punya akun? <a href="signup.php">Daftar di sini</a>
    </p>
  </div>
</div>

</body>
</html>
