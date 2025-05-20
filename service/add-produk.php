<?php
include '../service/conection.php';

if (isset($_POST['submit'])) {
    // Ambil dan sanitasi input
    $nama = mysqli_real_escape_string($conn, htmlspecialchars($_POST['nama']));
    $stok = (int) $_POST['stok'];
    $modal = (int) $_POST['modal'];
    $harga = (int) $_POST['harga'];
    $barcode = mysqli_real_escape_string($conn, htmlspecialchars($_POST['barcode']));
    $kategori_nama = mysqli_real_escape_string($conn, $_POST['kategori']);
    $deskripsi = mysqli_real_escape_string($conn, htmlspecialchars($_POST['deskripsi']));
    $keuntungan = $harga - $modal;

    // Validasi nama produk unik
    $cekNama = mysqli_query($conn, "SELECT * FROM produk WHERE nama_produk = '$nama'");
    if (mysqli_num_rows($cekNama) > 0) {
        echo "<script>alert('❌ Nama produk sudah digunakan!'); window.history.back();</script>";
        exit;
    }

    // Ambil ID kategori
    $kategori_sql = "SELECT id_kategori FROM kategori WHERE nama_kategori = '$kategori_nama'";
    $kategori_result = mysqli_query($conn, $kategori_sql);
    if (mysqli_num_rows($kategori_result) == 0) {
        echo "<script>alert('❌ Kategori tidak ditemukan!'); window.history.back();</script>";
        exit;
    }
    $kategori_data = mysqli_fetch_assoc($kategori_result);
    $kategori = $kategori_data['id_kategori'];

    // Proses upload gambar
    $gambar = $_FILES['gambar']['name'];
    $tmp = $_FILES['gambar']['tmp_name'];
    $upload_path = "../assets/" . basename($gambar);
    if (!move_uploaded_file($tmp, $upload_path)) {
        echo "<script>alert('❌ Gagal upload gambar!'); window.history.back();</script>";
        exit;
    }

    // Insert produk
    $sql = "INSERT INTO produk 
        (nama_produk, barcode, stok, modal, harga_jual, keuntungan, fid_kategori, gambar, deskripsi) 
        VALUES 
        ('$nama', '$barcode', '$stok', '$modal', '$harga', '$keuntungan', '$kategori', '$gambar', '$deskripsi')";
    
    if (mysqli_query($conn, $sql)) {
        header("Location: ../admin/produk.php");
        exit;
    } else {
        echo "<script>alert('❌ Gagal menyimpan produk!'); window.history.back();</script>";
    }
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Produk</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }

        body {
            background: linear-gradient(to right, #f1f1f1, #e0ffe0);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 30px 10px;
            min-height: 100vh;
            margin: 0;
        }

        .form-container {
            background: #fff;
            padding: 35px 30px;
            border-radius: 14px;
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.1);
            max-width: 450px;
            width: 100%;
        }

        .form-container h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
        }

        label {
            font-weight: 600;
            margin-bottom: 6px;
            display: block;
            color: #444;
        }

        input[type="text"],
        input[type="number"],
        input[type="file"],
        textarea,
        select {
            width: 100%;
            padding: 10px 12px;
            margin-bottom: 18px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        input:focus,
        textarea:focus,
        select:focus {
            border-color: #4CAF50;
            outline: none;
        }

        textarea {
            resize: vertical;
            min-height: 80px;
        }

        button {
            width: 100%;
            background-color: #4CAF50;
            color: white;
            padding: 12px;
            font-size: 16px;
            font-weight: bold;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #43a047;
        }

        @media (max-width: 480px) {
            .form-container {
                padding: 25px 20px;
            }
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>➕ Tambah Produk</h2>
    <form method="post" enctype="multipart/form-data">
        <label for="nama">Nama Produk</label>
        <input type="text" name="nama" id="nama" placeholder="Garmin 635" required>

        <label for="barcode">Barcode</label>
        <input type="text" name="barcode" id="barcode" placeholder="Contoh: 8991234567890" required>

        <label for="stok">Stok</label>
        <input type="number" name="stok" id="stok" placeholder="Contoh: 100" required>

        <label for="modal">Harga Modal (Rp)</label>
        <input type="number" name="modal" id="modal" placeholder="Contoh: 2500" required>

        <label for="harga">Harga Jual (Rp)</label>
        <input type="number" name="harga" id="harga" placeholder="Contoh: 3000" required>

        <label for="kategori">Kategori</label>
        <select name="kategori" id="kategori" required>
            <option value="">-- Pilih Kategori --</option>
            <?php
            $kategori_sql = "SELECT nama_kategori FROM kategori";
            $kategori_result = mysqli_query($conn, $kategori_sql);
            while ($kategori = mysqli_fetch_assoc($kategori_result)) {
                echo "<option value='" . $kategori['nama_kategori'] . "'>" . $kategori['nama_kategori'] . "</option>";
            }
            ?>
        </select>

        <label for="deskripsi">Deskripsi</label>
        <textarea name="deskripsi" id="deskripsi" placeholder="Contoh: Kelebihan dan Kekurangan" required></textarea>

        <label for="gambar">Gambar Produk</label>
        <input type="file" name="gambar" id="gambar" accept="image/*" required>

        <button type="submit" name="submit">Simpan Produk</button>
    </form>
</div>

</body>
</html>
