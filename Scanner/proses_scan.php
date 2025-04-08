<?php
$koneksi = mysqli_connect("localhost", "root", "", "kasir");

$kode = $_POST['barcode'];

$query = mysqli_query($koneksi, "SELECT * FROM produk WHERE barcode = '$kode'");
$data = mysqli_fetch_array($query);

if ($data) {
    echo "<h2>Produk Ditemukan</h2>";
    echo "<p>Nama Produk: <strong>" . $data['nama_produk'] . "</strong></p>";
    echo "<p>Harga Jual: Rp " . number_format($data['harga_jual']) . "</p>";
    echo "<p>Stok: " . $data['stok'] . "</p>";
} else {
    echo "<h2>Produk Tidak Ditemukan</h2>";
}
?>
