<?php
include '../service/conection.php';

$id = (int) $_GET['id'];
$data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM produk WHERE id_produk = $id"));

if (!$data) {
    echo "<script>alert('❌ Data produk tidak ditemukan!'); window.location.href='../admin/produk.php';</script>";
    exit;
}

if (isset($_POST['update'])) {
    $nama = mysqli_real_escape_string($conn, htmlspecialchars($_POST['nama']));
    $barcode = mysqli_real_escape_string($conn, htmlspecialchars($_POST['barcode']));
    $stok = (int) $_POST['stok'];
    $modal = (int) $_POST['modal'];
    $harga = (int) $_POST['harga'];
    $kategori = (int) $_POST['kategori'];
    $deskripsi = mysqli_real_escape_string($conn, htmlspecialchars($_POST['deskripsi']));
    $keuntungan = $harga - $modal;

    // Cek duplikat nama (selain produk yang sedang diedit)
    $cek_nama = mysqli_query($conn, "SELECT * FROM produk WHERE nama_produk = '$nama' AND id_produk != $id");
    if (mysqli_num_rows($cek_nama) > 0) {
        echo "<script>alert('❌ Nama produk sudah digunakan oleh produk lain!'); window.history.back();</script>";
        exit;
    }

    // Jika user upload gambar baru
    if ($_FILES['gambar']['name']) {
        $gambar = $_FILES['gambar']['name'];
        $tmp = $_FILES['gambar']['tmp_name'];
        $upload_path = "../assets/" . basename($gambar);
        if (!move_uploaded_file($tmp, $upload_path)) {
            echo "<script>alert('❌ Gagal upload gambar baru!'); window.history.back();</script>";
            exit;
        }

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
        // Tanpa ganti gambar
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

    if (mysqli_query($conn, $query)) {
        header("Location: ../admin/produk.php");
        exit;
    } else {
        echo "<script>alert('❌ Gagal mengupdate produk!'); window.history.back();</script>";
    }
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
            background-color: #f0f2f5;
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
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            width: 450px;
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
        select,
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

        .form-container img {
            width: 100%;
            max-height: 180px;
            object-fit: contain;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
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

        <label for="kategori">Kategori</label>
        <select name="kategori" id="kategori" required>
            <?php
            $kategori_sql = "SELECT * FROM kategori";
            $kategori_result = mysqli_query($conn, $kategori_sql);
            while ($kategori = mysqli_fetch_assoc($kategori_result)) {
                $selected = $kategori['id_kategori'] == $data['fid_kategori'] ? 'selected' : '';
                echo "<option value='{$kategori['id_kategori']}' $selected>{$kategori['nama_kategori']}</option>";
            }
            ?>
        </select>

        <label for="deskripsi">Deskripsi</label>
        <textarea name="deskripsi" id="deskripsi" rows="3" required><?= $data['deskripsi'] ?></textarea>

        <?php if ($data['gambar']): ?>
            <label>Gambar Saat Ini</label>
            <img src="../assets/<?= $data['gambar'] ?>" alt="Gambar Produk">
        <?php endif; ?>

        <label for="gambar">Ganti Gambar (Opsional)</label>
        <input type="file" name="gambar" id="gambar">

        <button type="submit" name="update">Update Produk</button>
    </form>
</div>

</body>
</html>
