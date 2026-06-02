<?php

session_start();
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/login.php');
}

$email    = trim($_POST['email']    ?? '');
$password = trim($_POST['password'] ?? '');

if (!$email || !$password) {
    setFlash('error', 'Email dan password tidak boleh kosong.');
    redirect('/login.php');
}

try {
    $stmt = $pdo->prepare("SELECT * FROM user WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        setFlash('success', 'Selamat datang kembali, ' . $user['name'] . '!');
        redirect('/pages/dasbor.php');
    } else {
        setFlash('error', 'Email atau password salah. Silakan coba lagi.');
        redirect('/login.php');
    }
} catch (PDOException $e) {
    setFlash('error', 'Terjadi kesalahan sistem. Coba lagi nanti.');
    redirect('/login.php');
}
