<?php
session_start();

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Hapus produk berdasarkan ID
    if (isset($_SESSION['keranjang'][$id])) {
        unset($_SESSION['keranjang'][$id]);
    }
}

header("Location: ../user/keranjang.php");
exit;
