<?php
include '../service/conection.php';

$id = $_GET['id'];
$data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM produk WHERE id_produk=$id"));

if (isset($_POST['update'])) {
    $nama = htmlspecialchars($_POST['nama']);
    $barcode = htmlspecialchars($_POST['barcode']);
    $stok = $_POST['stok'];
    $modal = $_POST['modal'];
    $harga = $_POST['harga'];
    $kategori = $_POST['kategori'];
    $deskripsi = htmlspecialchars($_POST['deskripsi']);
    $keuntungan = $harga - $modal;

    if ($_FILES['gambar']['name']) {
        $gambar = $_FILES['gambar']['name'];
        $tmp = $_FILES['gambar']['tmp_name'];
        move_uploaded_file($tmp, "../assets/" . $gambar);

        $query = "UPDATE produk SET 
            nama_produk='$nama',
            barcode='$barcode',
            stok='$stok',
            modal='$modal',
            harga_jual='$harga',
            keuntungan='$keuntungan',
            fid_kategori='$kategori',
            deskripsi='$deskripsi',
            gambar='$gambar' 
            WHERE id_produk=$id";
    } else {
        $query = "UPDATE produk SET 
            nama_produk='$nama',
            barcode='$barcode',
            stok='$stok',
            modal='$modal',
            harga_jual='$harga',
            keuntungan='$keuntungan',
            fid_kategori='$kategori',
            deskripsi='$deskripsi'
            WHERE id_produk=$id";
    }

    mysqli_query($conn, $query);
    header("Location: ../admin/produk.php");
}
?>

<!-- FORM UPDATE -->
<form method="post" enctype="multipart/form-data">
    <input type="text" name="nama" value="<?= $data['nama_produk'] ?>" required><br>
    <input type="text" name="barcode" value="<?= $data['barcode'] ?>" required><br>
    <input type="number" name="stok" value="<?= $data['stok'] ?>" required><br>
    <input type="number" name="modal" value="<?= $data['modal'] ?>" required><br>
    <input type="number" name="harga" value="<?= $data['harga_jual'] ?>" required><br>
    <input type="number" name="kategori" value="<?= $data['fid_kategori'] ?>" required><br>
    <textarea name="deskripsi" rows="3" required><?= $data['deskripsi'] ?></textarea><br>
    <input type="file" name="gambar"><br>
    <button type="submit" name="update">Update Produk</button>
</form>
