<?php
include '../service/conection.php';

if (isset($_POST['submit'])) {
    $nama = htmlspecialchars($_POST['nama']);
    $stok = $_POST['stok'];
    $modal = $_POST['modal'];
    $harga = $_POST['harga'];
    $barcode = htmlspecialchars($_POST['barcode']);
    $kategori_nama = $_POST['kategori'];  // Mengambil nama kategori
    $deskripsi = htmlspecialchars($_POST['deskripsi']);
    $keuntungan = $harga - $modal;

    // Cari ID Kategori berdasarkan Nama Kategori
    $kategori_sql = "SELECT id_kategori FROM kategori WHERE nama_kategori = '$kategori_nama'";
    $kategori_result = mysqli_query($conn, $kategori_sql);
    $kategori_data = mysqli_fetch_assoc($kategori_result);
    $kategori = $kategori_data['id_kategori'];

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


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Produk</title>
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
            background-color: #28a745;
            border: none;
            color: #fff;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Tambah Produk</h2>
    <form method="post" enctype="multipart/form-data">
        <label for="nama">Nama Produk</label>
        <input type="text" name="nama" id="nama" placeholder="Nama Produk" required>

        <label for="barcode">Barcode Produk</label>
        <input type="text" name="barcode" id="barcode" placeholder="Barcode" required>

        <label for="stok">Jumlah Stok</label>
        <input type="number" name="stok" id="stok" placeholder="Stok" required>

        <label for="modal">Harga Modal</label>
        <input type="number" name="modal" id="modal" placeholder="Modal" required>

        <label for="harga">Harga Jual</label>
        <input type="number" name="harga" id="harga" placeholder="Harga Jual" required>

        <label for="kategori">Nama Kategori</label>
<select name="kategori" id="kategori" required>
    <?php
    // Ambil semua kategori dari database
    $kategori_sql = "SELECT nama_kategori FROM kategori";
    $kategori_result = mysqli_query($conn, $kategori_sql);
    while ($kategori = mysqli_fetch_assoc($kategori_result)) {
        echo "<option value='" . $kategori['nama_kategori'] . "'>" . $kategori['nama_kategori'] . "</option>";
    }
    ?>
</select>


        <label for="deskripsi">Deskripsi</label>
        <textarea name="deskripsi" id="deskripsi" rows="3" placeholder="Deskripsi Produk" required></textarea>

        <label for="gambar">Gambar Produk</label>
        <input type="file" name="gambar" id="gambar" required>

        <button type="submit" name="submit">Tambah Produk</button>
    </form>
</div>

</body>
</html>
