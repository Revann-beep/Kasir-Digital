<?php
session_start();
include 'conection.php';

if (isset($_GET['id']) && isset($_GET['action'])) {
    $id = $_GET['id'];
    $action = $_GET['action'];

    // Ambil data produk dari database untuk cek stok
    $produk = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM produk WHERE id_produk = '$id'"));

    if (!$produk) {
        echo "<script>alert('Produk tidak ditemukan.');history.back();</script>";
        exit;
    }

    $stok_db = $produk['stok'];

    if (!isset($_SESSION['keranjang'][$id])) {
        echo "<script>alert('Produk tidak ada di keranjang.');history.back();</script>";
        exit;
    }

    $qty = $_SESSION['keranjang'][$id]['qty'];

    // Sesuaikan aksi berdasarkan tombol
    if ($action == 'lebih') {
        if ($qty + 1 > $stok_db) {
            echo "<script>alert('Stok tidak mencukupi.');history.back();</script>";
            exit;
        }
        $_SESSION['keranjang'][$id]['qty'] += 1;

    } elseif ($action == 'kurang') {
        $_SESSION['keranjang'][$id]['qty'] -= 1;
        if ($_SESSION['keranjang'][$id]['qty'] <= 0) {
            unset($_SESSION['keranjang'][$id]);
        }
    } else {
        echo "<script>alert('Aksi tidak dikenali.');history.back();</script>";
        exit;
    }

    // Perbarui waktu keranjang
    $_SESSION['keranjang_waktu'] = time();
}

// Redirect kembali ke halaman keranjang
header("Location: ../user/keranjang.php");
exit;