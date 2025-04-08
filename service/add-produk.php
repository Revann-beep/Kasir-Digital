<?php
include '../service/conection.php';

if (isset($_POST['submit'])) {
    $nama = htmlspecialchars($_POST['nama']);
    $stok = $_POST['stok'];
    $modal = $_POST['modal'];
    $harga = $_POST['harga'];
    $barcode = htmlspecialchars($_POST['barcode']);
    $kategori = $_POST['kategori'];
    $deskripsi = htmlspecialchars($_POST['deskripsi']);

    $keuntungan = $harga - $modal;

    // Upload gambar
    $gambar = $_FILES['gambar']['name'];
    $tmp = $_FILES['gambar']['tmp_name'];
    $upload_path = "../assets/" . $gambar;
    move_uploaded_file($tmp, $upload_path);

    $sql = "INSERT INTO produk 
    (nama_produk, barcode, stok, modal, harga_jual, keuntungan, fid_kategori, gambar, deskripsi) 
    VALUES 
    ('$nama', '$barcode', '$stok', '$modal', '$harga', '$keuntungan', '$kategori', '$gambar', '$deskripsi')";

    mysqli_query($conn, $sql);
    header("Location: ../admin/produk.php");
}
?>

<!-- FORM -->
<form method="post" enctype="multipart/form-data">
    <input type="text" name="nama" placeholder="Nama Produk" required><br>
    <input type="text" name="barcode" placeholder="Barcode Produk" required><br>
    <input type="number" name="stok" placeholder="Jumlah Stok" required><br>
    <input type="number" name="modal" placeholder="Harga Modal" required><br>
    <input type="number" name="harga" placeholder="Harga Jual" required><br>
    <input type="number" name="kategori" placeholder="ID Kategori" required><br>
    <textarea name="deskripsi" placeholder="Deskripsi Produk" rows="3" required></textarea><br>
    <input type="file" name="gambar" required><br>
    <button type="submit" name="submit">Tambah Produk</button>
</form>
