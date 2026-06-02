<?php

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /fashion-ims/login.php');
    exit;
}
require_once __DIR__ . '/../config/database.php';

if (($_SESSION['user_role'] ?? 'admin') !== 'admin') {
    setFlash('error', 'Anda tidak memiliki hak akses untuk menghapus produk!');
    redirect('/pages/produk.php');
}

$id = (int)($_GET['id'] ?? 0);
if (!$id) redirect('/pages/produk.php');

$query = $pdo->prepare("SELECT name, image FROM produk WHERE id = ?");
$query->execute([$id]);
$produk = $query->fetch();

if (!$produk) {
    setFlash('error', 'Produk tidak ditemukan.');
    redirect('/pages/produk.php');
}

$pdo->prepare("DELETE FROM produk WHERE id = ?")->execute([$id]);

if (!empty($produk['image']) && file_exists(UPLOAD_PATH . $produk['image'])) {
    @unlink(UPLOAD_PATH . $produk['image']);
}

setFlash('success', "Produk \"{$produk['name']}\" berhasil dihapus.");
redirect('/pages/produk.php');
