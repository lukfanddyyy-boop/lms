<?php
require_once 'session.php';
requireLogin();
require_once 'koneksi.php';

$userId = $_SESSION['user_id'];
$kelas  = $_SESSION['kelas'] ?? '';

$stmt = $pdo->query("SELECT * FROM sesi_kuis ORDER BY created_at DESC");
$semuaSesi = $stmt->fetchAll();

// Filter sesi yang relevan dengan kelas user
$sesiRelevan = array_filter($semuaSesi, function($s) use ($kelas) {
    if (empty($s['target_kelas'])) return true;
    $targets = explode(',', $s['target_kelas']);
    return in_array(trim($kelas), array_map('trim', $targets));
});

$pageTitle = 'Kuis';
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

<nav class="navbar">
  <a href="index.php" class="logo">🎓 Modul Ajar</a>
  <div class="nav-links">
    <a href="index.php">Modul</a>
    <a href="kuis.php" class="active">Kuis</a>
    <a href="rapor_siswa.php">Rapor</a>
    <a href="akun_saya.php">Profil</a>
    <?php if (isAdmin()): ?>
      <a href="management_kuis.php" class="badge-role">⚙ Admin</a>
    <?php endif; ?>
    <a href="logout.php" class="btn btn-outline btn-sm">Keluar</a>
  </div>
</nav>

<div class="container mt-3">
  <div class="flex-between mb-2">
    <div>
      <h2 class="section-title">📝 Daftar Kuis</h2>
      <p class="section-subtitle">Kerjakan kuis yang tersedia untuk menguji pemahaman kamu</p>
    </div>
  </div>

  <?php if (empty($sesiRelevan)): ?>
    <div class="empty-state">
      <span>📭</span>
      <p>Belum ada kuis tersedia untuk kelas kamu.</p>
    </div>
  <?php else: ?>
    <div class="table-wrapper">
      <table>
        <thead>
          <tr>
            <th>Nama Kuis</th>
            <th>Kategori</th>
            <th>Level</th>
            <th>Durasi</th>
            <th>Percobaan Maks</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($sesiRelevan as $s): ?>
            <?php
            // Hitung percobaan user
            $stmtCoba = $pdo->prepare("SELECT COUNT(*) FROM hasil_kuis WHERE user_id = ? AND sesi_id = ?");
            $stmtCoba->execute([$userId, $s['id']]);
            $coba = (int) $stmtCoba->fetchColumn();
            $maks = (int) $s['maks_percobaan'];
            $habis = $coba >= $maks;

            // Skor terbaik
            $stmtBest = $pdo->prepare("SELECT MAX(skor) FROM hasil_kuis WHERE user_id = ? AND sesi_id = ?");
            $stmtBest->execute([$userId, $s['id']]);
            $bestScore = $stmtBest->fetchColumn();
            ?>
            <tr>
              <td><strong><?= htmlspecialchars($s['label']) ?></strong></td>
              <td><?= htmlspecialchars($s['kategori']) ?></td>
              <td><span class="badge-level level-<?= strtolower($s['level']) ?>"><?= htmlspecialchars($s['level']) ?></span></td>
              <td><?= $s['durasi_menit'] ?> menit</td>
              <td>
                <?= $coba ?>/<?= $maks ?>
                <?php if ($habis): ?><span class="badge badge-gray">Habis</span><?php endif; ?>
              </td>
              <td>
                <?php if ($habis): ?>
                  <?php if ($bestScore !== false): ?>
                    <span class="badge badge-green">Terbaik: <?= number_format($bestScore, 1) ?></span>
                  <?php else: ?>
                    <span class="badge badge-gray">Selesai</span>
                  <?php endif; ?>
                <?php else: ?>
                  <a href="kerjakan_kuis.php?sesi=<?= $s['id'] ?>" class="btn btn-primary btn-sm">✏️ Kerjakan</a>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<footer class="footer">
  &copy; <?= date('Y') ?> LMS Modul Ajar
</footer>

</body>
</html>
