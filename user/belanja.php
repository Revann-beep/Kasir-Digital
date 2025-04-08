<!-- belanja.php -->
<?php
include '../service/conection.php';
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Belanja</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }

        .produk-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
        }

        .produk-item {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
        }

        .produk-item img {
            width: 150px;
            height: 150px;
            object-fit: cover;
        }

        .btn {
            background: #28a745;
            color: white;
            padding: 8px 12px;
            border: none;
            cursor: pointer;
            margin-top: 10px;
        }

        .btn:hover {
            background: #218838;
        }
    </style>
</head>
<body>

<h2>Daftar Produk</h2>
<div class="produk-container">
    <?php
    $query = "SELECT * FROM produk";
    $result = mysqli_query($conn, $query);
    while ($row = mysqli_fetch_assoc($result)) {
    ?>
        <div class="produk-item">
            <img src="../assets/<?= $row['gambar'] ?>" alt="<?= $row['nama_produk'] ?>">
            <h4><?= $row['nama_produk'] ?></h4>
            <p>Rp<?= number_format($row['harga_jual'], 0, ',', '.') ?></p>
            <form method="post" action="../service/tambah-keranjang.php">
    <input type="hidden" name="id" value="<?= $row['id_produk'] ?>">
    <button type="submit">🛒 Tambah ke Keranjang</button>
</form>
        </div>
    <?php } ?>
</div>

</body>
</html>
