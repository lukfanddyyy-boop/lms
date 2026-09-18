<?php
require_once 'session.php';
requireLogin();
require_once 'koneksi.php';

$user_id   = $_SESSION['user_id'];
$nama      = $_SESSION['nama_asli'] ?? $_SESSION['username'];
$kelas     = $_SESSION['kelas'] ?? '';

// Ambil semua modul aktif
$stmt = $pdo->query("SELECT * FROM modul WHERE aktif = 1 ORDER BY urutan ASC, created_at DESC");
$moduls = $stmt->fetchAll();

// Hitung statistik user
$stmtStat = $pdo->prepare("SELECT COUNT(*) FROM hasil_kuis WHERE user_id = ?");
$stmtStat->execute([$user_id]);
$total_kuis = $stmtStat->fetchColumn();

$stmtAvg = $pdo->prepare("SELECT AVG(skor) FROM hasil_kuis WHERE user_id = ?");
$stmtAvg->execute([$user_id]);
$rata_nilai = $stmtAvg->fetchColumn();

$pageTitle = 'Modul Ajar';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $pageTitle ?> — LMS</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
  <a href="index.php" class="logo">🎓 Modul Ajar</a>
  <div class="nav-links">
    <a href="index.php" class="active">Modul</a>
    <a href="kuis.php">Kuis</a>
    <a href="rapor_siswa.php">Rapor</a>
    <a href="akun_saya.php">Profil</a>
    <?php if (isAdmin()): ?>
      <a href="management_modul.php" class="badge-role">⚙ Admin</a>
    <?php endif; ?>
    <a href="logout.php" class="btn btn-outline btn-sm">Keluar</a>
  </div>
</nav>

<!-- HERO -->
<section class="hero">
  <h1>📚 Modul <span>Ajar</span></h1>
  <p>Belajar materi pelajaran secara terstruktur dan menyenangkan. Pilih modul yang ingin kamu pelajari.</p>
  <span class="badge badge-green">👋 Halo, <?= htmlspecialchars($nama) ?> — <?= htmlspecialchars($kelas) ?></span>
</section>

<!-- STATS -->
<div class="container">
  <div class="stats-row">
    <div class="stat-card">
      <div class="stat-value"><?= count($moduls) ?></div>
      <div class="stat-label">Modul Tersedia</div>
    </div>
    <div class="stat-card">
      <div class="stat-value"><?= (int)$total_kuis ?></div>
      <div class="stat-label">Kuis Dikerjakan</div>
    </div>
    <div class="stat-card">
      <div class="stat-value"><?= $rata_nilai ? number_format($rata_nilai, 1) : '-' ?></div>
      <div class="stat-label">Rata-Rata Nilai</div>
    </div>
  </div>
</div>

<!-- MODULE LIST -->
<div class="container">
  <div class="flex-between mb-2">
    <div>
      <h2 class="section-title">📖 Daftar Modul Ajar</h2>
      <p class="section-subtitle">Kelola seluruh materi dalam satu sistem</p>
    </div>
  </div>

  <?php if (empty($moduls)): ?>
    <div class="empty-state">
      <span>📭</span>
      <p>Belum ada modul tersedia. Silakan hubungi admin.</p>
    </div>
  <?php else: ?>
    <div class="module-grid">
      <?php foreach ($moduls as $m): ?>
        <div class="card module-card">
          <div class="card-header">
            <span>📄 <?= htmlspecialchars($m['judul']) ?></span>
            <span class="badge-level level-<?= strtolower($m['level']) ?>"><?= htmlspecialchars($m['level']) ?></span>
          </div>
          <div class="card-body">
            <p><?= htmlspecialchars(mb_strimwidth($m['deskripsi'] ?? 'Tidak ada deskripsi.', 0, 120, '...')) ?></p>
            <div class="module-meta">
              <span>📂 <?= htmlspecialchars($m['kategori']) ?></span>
              <?php if ($m['materi_tag']): ?>
                <span>🏷 <?= htmlspecialchars($m['materi_tag']) ?></span>
              <?php endif; ?>
            </div>
            <a href="buka_modul.php?id=<?= $m['id'] ?>" class="btn btn-primary btn-sm">📖 Buka Modul</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<!-- FOOTER -->
<footer class="footer">
  &copy; <?= date('Y') ?> LMS Modul Ajar — Dibuat dengan ❤️
</footer>

</body>
</html>
