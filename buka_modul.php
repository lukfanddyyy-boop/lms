<?php
require_once 'session.php';
requireLogin();
require_once 'koneksi.php';

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM modul WHERE id = ? AND aktif = 1");
$stmt->execute([$id]);
$modul = $stmt->fetch();

if (!$modul) {
    header('Location: index.php');
    exit;
}

$pageTitle = $modul['judul'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?> — LMS</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<nav class="navbar">
  <a href="index.php" class="logo">🎓 Modul Ajar</a>
  <div class="nav-links">
    <a href="index.php">Modul</a>
    <a href="kuis.php">Kuis</a>
    <a href="rapor_siswa.php">Rapor</a>
    <a href="akun_saya.php">Profil</a>
    <a href="logout.php" class="btn btn-outline btn-sm">Keluar</a>
  </div>
</nav>

<div class="container mt-3 mb-4">
  <a href="index.php" style="font-size:0.9rem;">← Kembali ke daftar modul</a>

  <div class="card mt-2">
    <div class="card-header" style="background:var(--green-50);">
      <span>📖 <?= htmlspecialchars($modul['judul']) ?></span>
      <span class="badge-level level-<?= strtolower($modul['level']) ?>"><?= htmlspecialchars($modul['level']) ?></span>
    </div>
    <div class="card-body">
      <div class="module-meta mb-3">
        <span>📂 <?= htmlspecialchars($modul['kategori']) ?></span>
        <?php if ($modul['materi_tag']): ?>
          <span>🏷 <?= htmlspecialchars($modul['materi_tag']) ?></span>
        <?php endif; ?>
      </div>

      <?php if ($modul['deskripsi']): ?>
        <div class="alert alert-info"><?= nl2br(htmlspecialchars($modul['deskripsi'])) ?></div>
      <?php endif; ?>

      <div class="modul-content" style="line-height:1.8; font-size:1rem;">
        <?= $modul['konten'] ? nl2br(htmlspecialchars($modul['konten'])) : '<p class="empty-state">Konten belum tersedia.</p>' ?>
      </div>
    </div>
  </div>

  <!-- Link ke kuis terkait -->
  <?php
  $stmtKuis = $pdo->prepare("SELECT s.id, s.label FROM sesi_kuis s WHERE s.kategori = ? OR s.materi_tag = ? LIMIT 5");
  $stmtKuis->execute([$modul['kategori'], $modul['materi_tag']]);
  $kuisTerkait = $stmtKuis->fetchAll();
  ?>
  <?php if ($kuisTerkait): ?>
  <div class="card mt-3">
    <div class="card-header">📝 Kuis Terkait</div>
    <div class="card-body">
      <ul style="list-style:none; padding:0;">
        <?php foreach ($kuisTerkait as $k): ?>
          <li style="padding:0.4rem 0; border-bottom:1px solid var(--gray-100);">
            <a href="kerjakan_kuis.php?sesi=<?= $k['id'] ?>">📋 <?= htmlspecialchars($k['label']) ?></a>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
  <?php endif; ?>
</div>

<footer class="footer">
  &copy; <?= date('Y') ?> LMS Modul Ajar
</footer>

</body>
</html>
