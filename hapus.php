<?php
require_once 'session.php';
requireAdmin();
require_once 'koneksi.php';

$type = $_GET['type'] ?? '';
$id   = (int)($_GET['id'] ?? 0);

if ($type === 'modul') {
    $pdo->prepare("DELETE FROM modul WHERE id = ?")->execute([$id]);
    header('Location: management_modul.php?sukses=hapus');
} elseif ($type === 'soal') {
    $pdo->prepare("DELETE FROM soal WHERE id = ?")->execute([$id]);
    header('Location: management_kuis.php?sukses=hapus');
} elseif ($type === 'user') {
    $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'")->execute([$id]);
    header('Location: management_user.php?sukses=hapus');
} else {
    header('Location: index.php');
}
exit;
