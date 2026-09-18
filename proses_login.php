<?php
require_once 'session.php';
require_once 'koneksi.php';
require_once 'csrf_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['login_error'] = 'Token keamanan tidak valid. Silakan coba lagi.';
    header('Location: login.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    $_SESSION['login_error'] = 'Username dan password wajib diisi.';
    header('Location: login.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
    session_regenerate_id(true);
    $_SESSION['user_id']   = $user['id'];
    $_SESSION['username']  = $user['username'];
    $_SESSION['nama_asli'] = $user['nama_asli'];
    $_SESSION['kelas']     = $user['kelas'];
    $_SESSION['role']      = $user['role'];
    $_SESSION['must_change_password'] = (bool) $user['must_change_password'];

    if ($user['must_change_password']) {
        header('Location: akun_saya.php?ganti=1');
    } else {
        header('Location: index.php');
    }
    exit;
} else {
    $_SESSION['login_error'] = 'Username atau password salah.';
    header('Location: login.php');
    exit;
}
