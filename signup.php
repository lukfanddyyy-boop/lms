<?php
require_once 'session.php';
require_once 'koneksi.php';

// Cek apakah registrasi terbuka
$stmt = $pdo->query("SELECT `value` FROM settings WHERE `key` = 'registrasi_terbuka'");
$reg_open = $stmt->fetchColumn() === '1';

if (!$reg_open) {
    die("<div class='container text-center mt-4'><h2>⛔ Registrasi sedang ditutup.</h2><p>Silakan hubungi admin.</p></div>");
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'csrf_helper.php';

    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Token keamanan tidak valid.';
    } else {
        $username  = trim($_POST['username'] ?? '');
        $nama_asli = trim($_POST['nama_asli'] ?? '');
        $kelas     = trim($_POST['kelas'] ?? '');
        $password  = $_POST['password'] ?? '';
        $password2 = $_POST['password2'] ?? '';

        if ($username === '' || $password === '') {
            $error = 'Username dan password wajib diisi.';
        } elseif ($password !== $password2) {
            $error = 'Konfirmasi password tidak cocok.';
        } elseif (strlen($password) < 6) {
            $error = 'Password minimal 6 karakter.';
        } else {
            $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmtCheck->execute([$username]);
            if ($stmtCheck->fetch()) {
                $error = 'Username sudah digunakan.';
            } else {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $stmtInsert = $pdo->prepare("INSERT INTO users (username, password, nama_asli, kelas, role) VALUES (?, ?, ?, ?, 'siswa')");
                $stmtInsert->execute([$username, $hash, $nama_asli, $kelas]);
                $success = 'Akun berhasil dibuat! <a href="login.php">Silakan masuk</a>.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar — LMS Modul Ajar</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body class="auth-page">

<div class="card auth-card">
  <div class="card-header">📝 Daftar Akun Baru</div>
  <div class="card-body">
    <?php if ($error): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="alert alert-success"><?= $success ?></div>
    <?php else: ?>
      <form method="POST">
        <?= csrfField() ?>
        <div class="form-group">
          <label for="username">Username</label>
          <input type="text" name="username" id="username" class="form-control" required>
        </div>
        <div class="form-group">
          <label for="nama_asli">Nama Lengkap</label>
          <input type="text" name="nama_asli" id="nama_asli" class="form-control">
        </div>
        <div class="form-group">
          <label for="kelas">Kelas</label>
          <input type="text" name="kelas" id="kelas" class="form-control" placeholder="Contoh: X TKJ 1">
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" name="password" id="password" class="form-control" required minlength="6">
        </div>
        <div class="form-group">
          <label for="password2">Konfirmasi Password</label>
          <input type="password" name="password2" id="password2" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary btn-lg" style="width:100%;">Daftar</button>
      </form>
    <?php endif; ?>
    <p class="text-center mt-2">
      Sudah punya akun? <a href="login.php">Masuk di sini</a>
    </p>
  </div>
</div>

</body>
</html>
