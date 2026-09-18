<?php
require_once 'session.php';
requireLogin();
require_once 'koneksi.php';

$hasilId = $_GET['id'] ?? 0;
$userId  = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT h.*, s.label as sesi_label FROM hasil_kuis h JOIN sesi_kuis s ON h.sesi_id = s.id WHERE h.id = ? AND h.user_id = ?");
$stmt->execute([$hasilId, $userId]);
$hasil = $stmt->fetch();

if (!$hasil) {
    header('Location: kuis.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hasil Kuis — LMS</title>
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

<div class="container mt-4" style="max-width:600px;">
  <div class="card">
    <div class="card-header" style="background:var(--green-50);">🎉 Hasil Kuis</div>
    <div class="card-body text-center">
      <h3><?= htmlspecialchars($hasil['sesi_label']) ?></h3>

      <div style="font-size:3.5rem; font-weight:800; color:var(--green-600); margin:1rem 0;">
        <?= number_format($hasil['skor'], 1) ?>
      </div>

      <div class="stats-row">
        <div class="stat-card">
          <div class="stat-value"><?= $hasil['benar'] ?>/<?= $hasil['total_soal'] ?></div>
          <div class="stat-label">Benar</div>
        </div>
        <div class="stat-card">
          <div class="stat-value">
            <?= $hasil['skor'] >= 75 ? '✅ Lulus' : '🔁 Coba Lagi' ?>
          </div>
          <div class="stat-label">Status</div>
        </div>
      </div>

      <p style="color:var(--gray-400); font-size:0.85rem;">
        Dikerjakan: <?= date('d M Y H:i', strtotime($hasil['selesai_pada'])) ?>
      </p>

      <div style="display:flex; gap:1rem; justify-content:center; margin-top:1.5rem;">
        <a href="kuis.php" class="btn btn-outline">📋 Kuis Lainnya</a>
        <a href="index.php" class="btn btn-primary">📖 Kembali ke Modul</a>
      </div>
    </div>
  </div>
</div>

<footer class="footer mt-4">
  &copy; <?= date('Y') ?> LMS Modul Ajar
</footer>

</body>
</html>
