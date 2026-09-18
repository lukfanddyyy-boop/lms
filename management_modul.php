<?php
require_once 'session.php';
requireAdmin();
require_once 'koneksi.php';

$moduls = $pdo->query("SELECT * FROM modul ORDER BY urutan ASC, created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Management Modul — Admin</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<nav class="navbar">
  <a href="index.php" class="logo">🎓 Modul Ajar</a>
  <div class="nav-links">
    <a href="index.php">Modul</a>
    <a href="management_modul.php" class="active badge-role">⚙ Admin Modul</a>
    <a href="management_kuis.php">Admin Kuis</a>
    <a href="management_user.php">Admin User</a>
    <a href="logout.php" class="btn btn-outline btn-sm">Keluar</a>
  </div>
</nav>

<div class="container mt-3">
  <div class="flex-between mb-2">
    <h2 class="section-title">📁 Management Modul</h2>
    <a href="tambah_modul.php" class="btn btn-primary">+ Tambah Modul</a>
  </div>

  <div class="table-wrapper">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Judul</th>
          <th>Kategori</th>
          <th>Level</th>
          <th>Tag</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($moduls as $m): ?>
          <tr>
            <td><?= $m['id'] ?></td>
            <td><strong><?= htmlspecialchars($m['judul']) ?></strong></td>
            <td><?= htmlspecialchars($m['kategori']) ?></td>
            <td><span class="badge-level level-<?= strtolower($m['level']) ?>"><?= htmlspecialchars($m['level']) ?></span></td>
            <td><?= htmlspecialchars($m['materi_tag'] ?? '-') ?></td>
            <td><?= $m['aktif'] ? '✅ Aktif' : '❌ Nonaktif' ?></td>
            <td>
              <a href="edit_modul.php?id=<?= $m['id'] ?>" class="btn btn-outline btn-sm">✏️</a>
              <a href="hapus.php?type=modul&id=<?= $m['id'] ?>"
                 class="btn btn-danger btn-sm"
                 onclick="return confirm('Yakin hapus modul ini?')">🗑</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<footer class="footer mt-4">&copy; <?= date('Y') ?> LMS Modul Ajar — Admin Panel</footer>
</body>
</html>
