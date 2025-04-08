<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Keranjang Belanja</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }

        h2 {
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }

        th {
            background-color: #f2b705;
            color: white;
        }

        .btn {
            padding: 5px 10px;
            text-decoration: none;
            border-radius: 5px;
            color: white;
        }

        .hapus {
            background-color: red;
        }

        .kosongkan {
            background-color: darkred;
            margin-top: 20px;
            display: inline-block;
        }
        .btn.kembali {
    display: inline-block;
    background-color: #ffcc00;
    color: black;
    padding: 8px 15px;
    border-radius: 5px;
    text-decoration: none;
    font-weight: bold;
    margin-bottom: 10px;
}
.btn.kembali:hover {
    background-color: #e6b800;
}
.btn.checkout {
    display: inline-block;
    background-color: #28a745;
    color: white;
    padding: 10px 18px;
    border-radius: 5px;
    text-decoration: none;
    font-weight: bold;
    float: right;
    margin-right: 20px;
}
.btn.checkout:hover {
    background-color: #218838;
}


    </style>
</head>
<body>
<a href="belanja.php" class="btn kembali">← Kembali ke Belanja</a>

    <h2>Keranjang Belanja</h2>

    <?php if (!empty($_SESSION['keranjang'])): ?>
        <table>
            <thead>
                <tr>
                    <th>Nama Produk</th>
                    <th>Harga</th>
                    <th>Qty</th>
                    <th>Subtotal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $total = 0;
                foreach ($_SESSION['keranjang'] as $item): 
                    $subtotal = $item['harga'] * $item['qty'];
                    $total += $subtotal;
                ?>
                    <tr>
                        <td><?= $item['nama'] ?></td>
                        <td>Rp <?= number_format($item['harga'], 0, ',', '.') ?></td>
                        <td><?= $item['qty'] ?></td>
                        <td>Rp <?= number_format($subtotal, 0, ',', '.') ?></td>
                        <td>
                        <a href="../service/hapus-keranjang.php?id=<?= $item['id_produk'] ?>" class="btn hapus">Hapus</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <th colspan="3">Total</th>
                    <th colspan="2">Rp <?= number_format($total, 0, ',', '.') ?></th>
                </tr>
            </tbody>
        </table>

        <a href="../service/hapus-semua-keranjang.php" class="btn kosongkan">🗑️ Kosongkan Keranjang</a>
        <a href="checkout.php" class="btn checkout">Checkout</a>

    <?php else: ?>
        <p style="text-align: center;">Keranjang masih kosong 😢</p>
    <?php endif; ?>
</body>
</html>
