<?php
session_start();
$koneksi = new mysqli("localhost", "root", "", "kasir");

$kode = $_POST['barcode'];

// Ambil data produk dari database
$query = $koneksi->prepare("SELECT * FROM produk WHERE barcode = ?");
$query->bind_param("s", $kode);
$query->execute();
$result = $query->get_result();
$produk = $result->fetch_assoc();

if ($produk) {
    $id_produk = $produk['id_produk'];
    $nama = $produk['nama_produk'];
    $harga = $produk['harga_jual'];

    // Cek apakah produk sudah ada di keranjang
    if (isset($_SESSION['keranjang'][$id_produk])) {
        $_SESSION['keranjang'][$id_produk]['qty'] += 1;
    } else {
        $_SESSION['keranjang'][$id_produk] = [
            'id_produk' => $id_produk,
            'nama' => $nama,
            'harga' => $harga,
            'qty' => 1
        ];
    }

    // Arahkan kembali ke halaman keranjang
    header("Location: ../user/keranjang.php");
    exit();
} else {
    echo "<h2>Produk Tidak Ditemukan</h2>";
    echo "<p><a href='scan.php'>🔄 Kembali ke Scan</a></p>";
}
?>
