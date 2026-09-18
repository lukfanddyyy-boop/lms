<?php
require_once 'session.php';
requireLogin();
require_once 'koneksi.php';

$userId = $_SESSION['user_id'];
$nama   = $_SESSION['nama_asli'] ?? $_SESSION['username'];
$kelas  = $_SESSION['kelas'] ?? '';

$stmt = $pdo->prepare("
    SELECT h.*, s.label as sesi_label, s.kategori, s.level
    FROM hasil_kuis h
    JOIN sesi_kuis s ON h.sesi_id = s.id
    WHERE h.user_id = ?
    ORDER BY h.selesai_pada DESC
");
$stmt->execute([$userId]);
$riwayat = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Rapor — LMS</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<nav class="navbar">
  <a href="index.php" class="logo">🎓 Modul Ajar</a>
  <div class="nav-links">
    <a href="index.php">Modul</a>
    <a href="kuis.php">Kuis</a>
    <a href="rapor_siswa.php" class="active">Rapor</a>
    <a href="akun_saya.php">Profil</a>
    <a href="logout.php" class="btn btn-outline btn-sm">Keluar</a>
  </div>
</nav>

<div class="container mt-3">
  <h2 class="section-title">📊 Rapor Nilai</h2>
  <p class="section-subtitle mb-2"><?= htmlspecialchars($nama) ?> — <?= htmlspecialchars($kelas) ?></p>

  <?php if (empty($riwayat)): ?>
    <div class="empty-state">
      <span>📭</span>
      <p>Belum ada nilai. Kerjakan kuis terlebih dahulu.</p>
    </div>
  <?php else: ?>
    <div class="table-wrapper">
      <table>
        <thead>
          <tr>
            <th>Kuis</th>
            <th>Kategori</th>
            <th>Level</th>
            <th>Benar</th>
            <th>Skor</th>
            <th>Tanggal</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($riwayat as $r): ?>
            <tr>
              <td><strong><?= htmlspecialchars($r['sesi_label']) ?></strong></td>
              <td><?= htmlspecialchars($r['kategori']) ?></td>
              <td><span class="badge-level level-<?= strtolower($r['level']) ?>"><?= htmlspecialchars($r['level']) ?></span></td>
              <td><?= $r['benar'] ?>/<?= $r['total_soal'] ?></td>
              <td>
                <strong style="color:<?= $r['skor'] >= 75 ? 'var(--green-600)' : '#ef4444' ?>;">
                  <?= number_format($r['skor'], 1) ?>
                </strong>
              </td>
              <td><?= date('d M Y', strtotime($r['selesai_pada'])) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<footer class="footer mt-4">
  &copy; <?= date('Y') ?> LMS Modul Ajar
</footer>

</body>
</html>
