<?php
session_start();
include '../service/conection.php';

if (isset($_POST['id'])) {
    $id = $_POST['id'];

    // Ambil data produk dari database
    $query = "SELECT * FROM produk WHERE id_produk = '$id'";
    $result = mysqli_query($conn, $query);
    $produk = mysqli_fetch_assoc($result);

    if ($produk) {
        // Tambahkan ke keranjang (session)
        if (isset($_SESSION['keranjang'][$id])) {
            $_SESSION['keranjang'][$id]['qty'] += 1;
        } else {
            $_SESSION['keranjang'][$id] = [
                'id_produk' => $produk['id_produk'],
                'nama' => $produk['nama_produk'],
                'harga' => $produk['harga_jual'],
                'qty' => 1
            ];
        }
    } else {
        echo "Produk tidak ditemukan.";
        exit;
    }
}

header("Location: ../user/keranjang.php");
exit;
?>