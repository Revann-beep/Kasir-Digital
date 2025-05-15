<?php
session_start();
$koneksi = new mysqli("localhost", "root", "", "kasir");

if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}

$kode = $_POST['barcode'] ?? '';

if (empty($kode)) {
    echo "<script>
            alert('Barcode tidak boleh kosong!');
            window.location.href = 'scan.php';
          </script>";
    exit();
}

// Ambil data produk
$query = $koneksi->prepare("SELECT id_produk, nama_produk, harga_jual, stok FROM produk WHERE barcode = ?");
$query->bind_param("s", $kode);
$query->execute();
$result = $query->get_result();
$produk = $result->fetch_assoc();

if (!$produk) {
    echo "<script>
            alert('Produk tidak ditemukan!');
            window.location.href = 'scan.php';
          </script>";
    exit();
}

$id_produk = $produk['id_produk'];
$nama = $produk['nama_produk'];
$harga = $produk['harga_jual'];
$stok = $produk['stok'];

// Cek jumlah di keranjang
$qty_di_keranjang = $_SESSION['keranjang'][$id_produk]['qty'] ?? 0;

// Validasi stok
if ($stok <= 0) {
    echo "<script>
            alert('Stok produk \"$nama\" kosong!');
            window.location.href = 'scan.php';
          </script>";
    exit();
}

if ($qty_di_keranjang >= $stok) {
    echo "<script>
            alert('Stok produk \"$nama\" tidak mencukupi! Tersisa $stok.');
            window.location.href = 'scan.php';
          </script>";
    exit();
}

// Tambah ke keranjang
if ($qty_di_keranjang > 0) {
    $_SESSION['keranjang'][$id_produk]['qty'] += 1;
} else {
    $_SESSION['keranjang'][$id_produk] = [
        'id_produk' => $id_produk,
        'nama' => $nama,
        'harga' => $harga,
        'qty' => 1
    ];
}

// Redirect ke keranjang
header("Location: ../user/keranjang.php");
exit();
