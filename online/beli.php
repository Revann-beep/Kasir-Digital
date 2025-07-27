<?php
session_start();
$id = $_GET['id'];

// Tambah ke keranjang
if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}

if (isset($_SESSION['keranjang'][$id])) {
    $_SESSION['keranjang'][$id] += 1;
} else {
    $_SESSION['keranjang'][$id] = 1;
}

// Kembali ke daftar produk
header("Location: produkonline.php");
exit;
