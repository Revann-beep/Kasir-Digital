<?php
session_start();
include '../service/conection.php';

// Simulasi admin login (hapus ini setelah sistem login aktif!)
$_SESSION['id_admin'] = 1; // Ganti dengan ID admin yang benar di database


$id_admin = $_SESSION['id_admin'];
$fid_member = isset($_SESSION['fid_member']) ? $_SESSION['fid_member'] : 'NULL';

$total = 0;
foreach ($_SESSION['keranjang'] as $id_produk => $qty) {
  $id_produk = (int) $id_produk; // pastikan angka
  $qty = (int) $qty;

  $query = mysqli_query($conn, "SELECT harga_jual FROM produk WHERE id_produk = $id_produk");
  if (!$query) {
    die("Produk tidak ditemukan (ID: $id_produk)");
  }

  $data = mysqli_fetch_assoc($query);
  $subtotal = $data['harga_jual'] * $qty;
  $total += $subtotal;
}

// === DISKON DARI POIN ===
$diskon = 0;
if ($fid_member !== 'NULL') {
  $query_poin = mysqli_query($conn, "SELECT poin FROM member WHERE id_member = $fid_member");
  if ($query_poin && mysqli_num_rows($query_poin) > 0) {
    $data_poin = mysqli_fetch_assoc($query_poin);
    $maks_diskon = $data_poin['poin'] * 100; // 1 poin = Rp100
    $diskon = min($maks_diskon, $total);
    $total -= $diskon;

    // Kurangi poin yang digunakan
    $poin_dipakai = floor($diskon / 100);
    mysqli_query($conn, "UPDATE member SET poin = poin - $poin_dipakai WHERE id_member = $fid_member");
  }
}

// === INSERT TRANSAKSI ===
mysqli_query($conn, "
  INSERT INTO transaksi (tgl_pembelian, total_harga, fid_admin, fid_member)
  VALUES (NOW(), $total, $id_admin, $fid_member)
");
$id_transaksi = mysqli_insert_id($conn);

// === INSERT DETAIL TRANSAKSI ===
foreach ($_SESSION['keranjang'] as $id_produk => $qty) {
  $id_produk = (int) $id_produk;
  $qty = (int) $qty;

  $query = mysqli_query($conn, "SELECT harga_jual FROM produk WHERE id_produk = $id_produk");
  $data = mysqli_fetch_assoc($query);
  $harga = $data['harga_jual'];
  $subtotal = $harga * $qty;

  mysqli_query($conn, "
    INSERT INTO detail_transaksi (fid_transaksi, fid_produk, qty, harga, subtotal)
    VALUES ($id_transaksi, $id_produk, $qty, $harga, $subtotal)
  ");
}

// === TAMBAH POIN DARI TOTAL BARU (Rp1.000 = 1 poin) ===
if ($fid_member !== 'NULL') {
  $poin_baru = floor($total / 1000);
  mysqli_query($conn, "UPDATE member SET poin = poin + $poin_baru WHERE id_member = $fid_member");
}

// === HAPUS KERANJANG DAN REDIRECT ===
unset($_SESSION['keranjang']);
header("Location: invoice.php?id_transaksi=$id_transaksi");
exit;
?>
