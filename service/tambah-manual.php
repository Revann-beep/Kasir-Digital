<?php
session_start();
include 'conection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_produk = (int)$_POST['produk_id'];
    $qty = (int)$_POST['qty'];

    // Ambil data produk dari DB
    $q = mysqli_query($conn, "SELECT * FROM produk WHERE id_produk=$id_produk");
    if (mysqli_num_rows($q) == 0) {
        die("Produk tidak ditemukan.");
    }

    $produk = mysqli_fetch_assoc($q);

    // Batasi maksimal 5 produk berbeda di keranjang
    if (!isset($_SESSION['keranjang'])) $_SESSION['keranjang'] = [];

    if (!isset($_SESSION['keranjang'][$id_produk]) && count($_SESSION['keranjang']) >= 5) {
        echo "<script>alert('Maksimal 5 produk di keranjang.'); window.location='../user/keranjang.php';</script>";
        exit;
    }

    // Tambahkan ke keranjang atau update qty
    if (isset($_SESSION['keranjang'][$id_produk])) {
        $_SESSION['keranjang'][$id_produk]['qty'] += $qty;
    } else {
        $_SESSION['keranjang'][$id_produk] = [
            'id_produk' => $produk['id_produk'],
            'nama' => $produk['nama'],
            'harga' => $produk['harga_jual'],
            'qty' => $qty
        ];
    }

    // Atur waktu keranjang jika belum ada
    if (!isset($_SESSION['keranjang_waktu'])) {
        $_SESSION['keranjang_waktu'] = time();
    }

    header("Location: ../user/keranjang.php");
}
?>
