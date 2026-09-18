<?php
require_once 'session.php';
requireAdmin();
require_once 'koneksi.php';
require_once 'csrf_helper.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Token tidak valid.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO modul (judul, deskripsi, konten, kategori, level, materi_tag, urutan) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['judul'],
            $_POST['deskripsi'],
            $_POST['konten'],
            $_POST['kategori'],
            $_POST['level'],
            $_POST['materi_tag'],
            (int)($_POST['urutan'] ?? 0),
        ]);
        header('Location: management_modul.php?sukses=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tambah Modul — Admin</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<nav class="navbar">
  <a href="index.php" class="logo">🎓 Modul Ajar</a>
  <div class="nav-links">
    <a href="management_modul.php">← Kembali</a>
    <a href="logout.php" class="btn btn-outline btn-sm">Keluar</a>
  </div>
</nav>

<div class="container mt-3" style="max-width:700px;">
  <h2 class="section-title mb-2">➕ Tambah Modul Baru</h2>

  <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

  <div class="card">
    <div class="card-body">
      <form method="POST">
        <?= csrfField() ?>
        <div class="form-group">
          <label for="judul">Judul Modul *</label>
          <input type="text" name="judul" id="judul" class="form-control" required>
        </div>
        <div class="form-group">
          <label for="kategori">Kategori</label>
          <input type="text" name="kategori" id="kategori" class="form-control" value="Umum">
        </div>
        <div class="form-group">
          <label for="level">Level</label>
          <select name="level" id="level" class="form-control">
            <option value="Pemula">Pemula</option>
            <option value="Menengah">Menengah</option>
            <option value="Lanjutan">Lanjutan</option>
          </select>
        </div>
        <div class="form-group">
          <label for="materi_tag">Tag Materi</label>
          <input type="text" name="materi_tag" id="materi_tag" class="form-control" placeholder="Contoh: Subnetting">
        </div>
        <div class="form-group">
          <label for="deskripsi">Deskripsi</label>
          <textarea name="deskripsi" id="deskripsi" class="form-control" rows="3"></textarea>
        </div>
        <div class="form-group">
          <label for="konten">Konten Materi</label>
          <textarea name="konten" id="konten" class="form-control" rows="10" placeholder="Tulis materi di sini..."></textarea>
        </div>
        <div class="form-group">
          <label for="urutan">Urutan</label>
          <input type="number" name="urutan" id="urutan" class="form-control" value="0">
        </div>
        <button type="submit" class="btn btn-primary btn-lg" style="width:100%;">💾 Simpan Modul</button>
      </form>
    </div>
  </div>
</div>

<footer class="footer mt-4">&copy; <?= date('Y') ?> LMS Modul Ajar — Admin Panel</footer>
</body>
</html>
