<?php
require_once 'session.php';
requireLogin();
require_once 'koneksi.php';
require_once 'csrf_helper.php';

$userId = $_SESSION['user_id'];
$sesiId = $_GET['sesi'] ?? 0;

// Validasi sesi
$stmt = $pdo->prepare("SELECT * FROM sesi_kuis WHERE id = ?");
$stmt->execute([$sesiId]);
$sesi = $stmt->fetch();
if (!$sesi) { header('Location: kuis.php'); exit; }

// Cek jumlah percobaan
$stmtCoba = $pdo->prepare("SELECT COUNT(*) FROM hasil_kuis WHERE user_id = ? AND sesi_id = ?");
$stmtCoba->execute([$userId, $sesiId]);
if ((int)$stmtCoba->fetchColumn() >= (int)$sesi['maks_percobaan']) {
    header('Location: kuis.php');
    exit;
}

// Ambil soal sesuai filter sesi
$sqlSoal = "SELECT * FROM soal WHERE 1=1";
$params = [];
if ($sesi['kategori'])   { $sqlSoal .= " AND kategori = ?"; $params[] = $sesi['kategori']; }
if ($sesi['level'])      { $sqlSoal .= " AND level = ?";    $params[] = $sesi['level']; }
if ($sesi['materi_tag']) { $sqlSoal .= " AND materi_tag = ?"; $params[] = $sesi['materi_tag']; }
$sqlSoal .= " ORDER BY RAND()";

$stmtSoal = $pdo->prepare($sqlSoal);
$stmtSoal->execute($params);
$soals = $stmtSoal->fetchAll();

if (empty($soals)) {
    die("<div class='container mt-4'><div class='alert alert-warning'>Tidak ada soal tersedia untuk kuis ini.</div><a href='kuis.php' class='btn btn-outline'>← Kembali</a></div>");
}

// Pin soal ke session (anti-tamper)
$_SESSION['quiz_soal_ids'] = array_column($soals, 'id');
$_SESSION['quiz_sesi_id']  = $sesiId;
$_SESSION['quiz_start']    = time();

$pageTitle = $sesi['label'];
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
    <span style="color:var(--gray-500);">⏱ <span id="timer"><?= $sesi['durasi_menit'] ?>:00</span></span>
    <a href="logout.php" class="btn btn-outline btn-sm">Keluar</a>
  </div>
</nav>

<div class="container quiz-container mt-3">
  <div class="flex-between mb-2">
    <h2 class="section-title">📝 <?= htmlspecialchars($sesi['label']) ?></h2>
    <span class="badge badge-green"><?= count($soals) ?> soal</span>
  </div>

  <form action="save_quiz.php" method="POST" id="quizForm">
    <?= csrfField() ?>
    <input type="hidden" name="sesi_id" value="<?= $sesiId ?>">

    <?php foreach ($soals as $i => $soal): ?>
      <div class="quiz-question">
        <div class="q-number"><?= $i + 1 ?></div>
        <p style="font-weight:600; margin-bottom:0.75rem;"><?= htmlspecialchars($soal['pertanyaan']) ?></p>

        <?php if ($soal['jenis'] === 'pilihan_ganda'): ?>
          <?php foreach (['a','b','c','d','e'] as $opt): ?>
            <?php if ($soal["pilihan_$opt"]): ?>
              <label class="quiz-option">
                <input type="radio" name="jawaban[<?= $soal['id'] ?>]" value="<?= $opt ?>" required>
                <strong><?= strtoupper($opt) ?>.</strong> <?= htmlspecialchars($soal["pilihan_$opt"]) ?>
              </label>
            <?php endif; ?>
          <?php endforeach; ?>
        <?php else: ?>
          <input type="text" name="jawaban[<?= $soal['id'] ?>]" class="form-control"
                 placeholder="Tulis jawaban kamu..." required>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>

    <button type="submit" class="btn btn-primary btn-lg" style="width:100%;">✅ Kumpulkan Jawaban</button>
  </form>
</div>

<!-- Timer Script -->
<script>
const durasiMenit = <?= $sesi['durasi_menit'] ?>;
let waktu = durasiMenit * 60;
const timerEl = document.getElementById('timer');

const interval = setInterval(() => {
  waktu--;
  const m = Math.floor(waktu / 60);
  const s = waktu % 60;
  timerEl.textContent = m + ':' + String(s).padStart(2, '0');

  if (waktu <= 60) timerEl.style.color = '#ef4444';

  if (waktu <= 0) {
    clearInterval(interval);
    alert('⏰ Waktu habis! Jawaban akan otomatis dikumpulkan.');
    document.getElementById('quizForm').submit();
  }
}, 1000);
</script>

<footer class="footer mt-4">
  &copy; <?= date('Y') ?> LMS Modul Ajar
</footer>

</body>
</html>
