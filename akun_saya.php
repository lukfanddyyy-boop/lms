<?php
require_once 'session.php';
requireLogin();
require_once 'koneksi.php';
require_once 'csrf_helper.php';

$userId = $_SESSION['user_id'];
$pesan  = '';
$error  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Token tidak valid.';
    } else {
        $nama_asli = trim($_POST['nama_asli'] ?? '');
        $kelas     = trim($_POST['kelas'] ?? '');
        $pw_lama   = $_POST['password_lama'] ?? '';
        $pw_baru   = $_POST['password_baru'] ?? '';

        $stmt = $pdo->prepare("UPDATE users SET nama_asli = ?, kelas = ? WHERE id = ?");
        $stmt->execute([$nama_asli, $kelas, $userId]);
        $_SESSION['nama_asli'] = $nama_asli;
        $_SESSION['kelas']     = $kelas;

        if ($pw_baru !== '') {
            $stmtUser = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmtUser->execute([$userId]);
            $currentHash = $stmtUser->fetchColumn();

            if (!password_verify($pw_lama, $currentHash)) {
                $error = 'Password lama salah.';
            } elseif (strlen($pw_baru) < 6) {
                $error = 'Password baru minimal 6 karakter.';
            } else {
                $hash = password_hash($pw_baru, PASSWORD_BCRYPT);
                $pdo->prepare("UPDATE users SET password = ?, must_change_password = 0 WHERE id = ?")->execute([$hash, $userId]);
                $_SESSION['must_change_password'] = false;
                $pesan = 'Password berhasil diubah.';
            }
        } else {
            $pesan = 'Profil berhasil diperbarui.';
        }
    }
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profil — LMS</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<nav class="navbar">
  <a href="index.php" class="logo">🎓 Modul Ajar</a>
  <div class="nav-links">
    <a href="index.php">Modul</a>
    <a href="kuis.php">Kuis</a>
    <a href="rapor_siswa.php">Rapor</a>
    <a href="akun_saya.php" class="active">Profil</a>
    <a href="logout.php" class="btn btn-outline btn-sm">Keluar</a>
  </div>
</nav>

<div class="container mt-3" style="max-width:600px;">
  <h2 class="section-title mb-2">👤 Profil Saya</h2>

  <?php if ($pesan): ?><div class="alert alert-success"><?= htmlspecialchars($pesan) ?></div><?php endif; ?>
  <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

  <?php if (mustChangePassword()): ?>
    <div class="alert alert-warning">⚠️ Kamu harus mengganti password sebelum melanjutkan.</div>
  <?php endif; ?>

  <div class="card">
    <div class="card-body">
      <form method="POST">
        <?= csrfField() ?>
        <div class="form-group">
          <label>Username</label>
          <input type="text" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" disabled>
        </div>
        <div class="form-group">
          <label for="nama_asli">Nama Lengkap</label>
          <input type="text" name="nama_asli" id="nama_asli" class="form-control"
                 value="<?= htmlspecialchars($user['nama_asli'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label for="kelas">Kelas</label>
          <input type="text" name="kelas" id="kelas" class="form-control"
                 value="<?= htmlspecialchars($user['kelas'] ?? '') ?>">
        </div>
        <hr style="margin:1.5rem 0; border-color:var(--gray-100);">
        <p style="font-weight:700; margin-bottom:0.75rem;">🔒 Ganti Password</p>
        <div class="form-group">
          <label for="password_lama">Password Lama</label>
          <input type="password" name="password_lama" id="password_lama" class="form-control">
        </div>
        <div class="form-group">
          <label for="password_baru">Password Baru</label>
          <input type="password" name="password_baru" id="password_baru" class="form-control" minlength="6">
        </div>
        <button type="submit" class="btn btn-primary btn-lg" style="width:100%;">💾 Simpan Perubahan</button>
      </form>
    </div>
  </div>
</div>

<footer class="footer mt-4">
  &copy; <?= date('Y') ?> LMS Modul Ajar
</footer>

</body>
</html>
