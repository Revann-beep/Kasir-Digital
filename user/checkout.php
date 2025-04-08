<?php
session_start();
include '../service/conection.php';

// Ambil data dari session keranjang
$keranjang = $_SESSION['keranjang'];
$fid_member = isset($_POST['fid_member']) ? $_POST['fid_member'] : 'NULL';
$fid_admin = 1; // Ganti sesuai login admin
$tgl_pembelian = date("Y-m-d");

// Hitung total
$total_harga = 0;
foreach ($keranjang as $item) {
    $total_harga += $item['harga'] * $item['qty'];
}

// Simpan ke tabel transaksi
mysqli_query($conn, "INSERT INTO transaksi (tgl_pembelian, total_harga, fid_admin, fid_member) VALUES ('$tgl_pembelian', '$total_harga', $fid_admin, $fid_member)");
$id_transaksi = mysqli_insert_id($conn); // Ambil ID transaksi terakhir

// Simpan ke tabel detail_transaksi
foreach ($keranjang as $item) {
    $id_produk = $item['id_produk'];
    $qty = $item['qty'];
    $harga = $item['harga'];
    $subtotal = $qty * $harga;

    mysqli_query($conn, "INSERT INTO detail_transaksi (fid_transaksi, fid_produk, qty, harga, subtotal) VALUES ($id_transaksi, $id_produk, $qty, $harga, $subtotal)");
    
    // Update stok
    mysqli_query($conn, "UPDATE produk SET stok = stok - $qty WHERE id_produk = $id_produk");
}

// Tambah poin ke member (opsional)
if ($fid_member !== 'NULL') {
    $poin = floor($total_harga / 10000); // 1 poin per 10rb
    mysqli_query($conn, "UPDATE member SET point = point + $poin WHERE id_member = $fid_member");
}

// Hapus session keranjang
unset($_SESSION['keranjang']);

// Redirect atau tampilkan invoice
header("Location: invoice.php?id_transaksi=$id_transaksi");
exit;
?>
