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

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Update Produk</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f6f8;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .form-container {
            background-color: #fff;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            width: 400px;
        }

        .form-container h2 {
            margin-bottom: 20px;
            text-align: center;
            color: #333;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #555;
        }

        input[type="text"],
        input[type="number"],
        input[type="file"],
        textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
        }

        textarea {
            resize: vertical;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            border: none;
            color: #fff;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Update Produk</h2>
    <form method="post" enctype="multipart/form-data">
        <label for="nama">Nama Produk</label>
        <input type="text" name="nama" id="nama" value="<?= $data['nama_produk'] ?>" required>

        <label for="barcode">Barcode</label>
        <input type="text" name="barcode" id="barcode" value="<?= $data['barcode'] ?>" required>

        <label for="stok">Stok</label>
        <input type="number" name="stok" id="stok" value="<?= $data['stok'] ?>" required>

        <label for="modal">Harga Modal</label>
        <input type="number" name="modal" id="modal" value="<?= $data['modal'] ?>" required>

        <label for="harga">Harga Jual</label>
        <input type="number" name="harga" id="harga" value="<?= $data['harga_jual'] ?>" required>

        <label for="kategori">ID Kategori</label>
        <input type="number" name="kategori" id="kategori" value="<?= $data['fid_kategori'] ?>" required>

        <label for="deskripsi">Deskripsi</label>
        <textarea name="deskripsi" id="deskripsi" rows="3" required><?= $data['deskripsi'] ?></textarea>

        <label for="gambar">Gambar Produk</label>
        <input type="file" name="gambar" id="gambar">

        <button type="submit" name="update">Update Produk</button>
    </form>
</div>

</body>
</html>
