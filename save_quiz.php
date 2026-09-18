<?php
require_once 'session.php';
requireLogin();
require_once 'koneksi.php';
require_once 'csrf_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: kuis.php');
    exit;
}

if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
    die('Token keamanan tidak valid.');
}

$sesiId   = (int)($_POST['sesi_id'] ?? 0);
$userId   = $_SESSION['user_id'];
$jawaban  = $_POST['jawaban'] ?? [];

// Validasi soal yang diizinkan
$pinnedIds = $_SESSION['quiz_soal_ids'] ?? [];
if (empty($pinnedIds) || $_SESSION['quiz_sesi_id'] != $sesiId) {
    die('Sesi kuis tidak valid.');
}

// Ambil soal dari pinned IDs
$placeholders = implode(',', array_fill(0, count($pinnedIds), '?'));
$stmt = $pdo->prepare("SELECT * FROM soal WHERE id IN ($placeholders)");
$stmt->execute($pinnedIds);
$soals = $stmt->fetchAll();

$benar  = 0;
$total  = count($soals);
$detailJawaban = [];

foreach ($soals as $soal) {
    $sid    = $soal['id'];
    $jwb    = trim($jawaban[$sid] ?? '');
    $cocok  = false;

    if ($soal['jenis'] === 'pilihan_ganda') {
        $cocok = strtoupper($jwb) === strtoupper($soal['jawaban_benar']);
    } else {
        // Isian pendek — normalisasi
        $normalJwb = strtolower(trim(preg_replace('/[^\w\s]/', '', $jwb)));
        $diterima  = array_map(function($v) {
            return strtolower(trim(preg_replace('/[^\w\s]/', '', $v)));
        }, explode("\n", $soal['jawaban_diterima'] ?? ''));
        $cocok = in_array($normalJwb, $diterima);
    }

    if ($cocok) $benar++;
    $detailJawaban[] = ['soal_id' => $sid, 'jawaban' => $jwb, 'cocok' => $cocok];
}

$skor = $total > 0 ? round(($benar / $total) * 100, 2) : 0;

// Simpan hasil
$pdo->beginTransaction();
try {
    $stmtInsert = $pdo->prepare("INSERT INTO hasil_kuis (user_id, sesi_id, skor, total_soal, benar) VALUES (?, ?, ?, ?, ?)");
    $stmtInsert->execute([$userId, $sesiId, $skor, $total, $benar]);
    $hasilId = $pdo->lastInsertId();

    $stmtDetail = $pdo->prepare("INSERT INTO jawaban_detail (hasil_id, soal_id, jawaban_siswa, cocok) VALUES (?, ?, ?, ?)");
    foreach ($detailJawaban as $d) {
        $stmtDetail->execute([$hasilId, $d['soal_id'], $d['jawaban'], (int)$d['cocok']]);
    }

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Quiz save error: " . $e->getMessage());
    die('Gagal menyimpan hasil kuis.');
}

// Bersihkan session
unset($_SESSION['quiz_soal_ids'], $_SESSION['quiz_sesi_id'], $_SESSION['quiz_start']);

// Redirect ke hasil
header("Location: hasil_kuis.php?id=$hasilId");
exit;
