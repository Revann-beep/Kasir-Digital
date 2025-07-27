<?php
session_start();
include '../service/conection.php';

// Ambil keranjang dari session (hanya ID dan jumlah)
$keranjang = $_SESSION['keranjang'] ?? [];

$keranjangProduk = [];
$total = 0;

// Ambil detail produk dari database
if (!empty($keranjang)) {
    $ids = implode(",", array_keys($keranjang));
    $result = mysqli_query($conn, "SELECT * FROM produk WHERE id_produk IN ($ids)");
    
    while ($produk = mysqli_fetch_assoc($result)) {
        $id = $produk['id_produk'];
        $qty = $keranjang[$id];
        $subtotal = $produk['harga_jual'] * $qty;
        $total += $subtotal;

        $keranjangProduk[] = [
            'nama' => $produk['nama_produk'],
            'harga' => $produk['harga_jual'],
            'qty' => $qty,
            'subtotal' => $subtotal
        ];
    }
}

// Cek jika user member
$isMember = $_SESSION['member'] ?? false;
$diskon = 0;
if ($isMember) {
    $diskon = min($total * 0.1, $_SESSION['member']['poin'] * 1000); // max 10%
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Checkout</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 0;
        }
        .checkout-container {
            max-width: 800px;
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        }
        h2 {
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .total-row {
            font-weight: 600;
        }
        .btn-checkout {
            padding: 12px 20px;
            background-color: #1f8ef1;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
        }
        .btn-checkout:hover {
            background-color: #0d74d1;
        }
    </style>
</head>
<body>
    <div class="checkout-container">
        <h2>Checkout</h2>
        <?php if (empty($keranjang)) : ?>
            <p>Keranjang kosong.</p>
        <?php else : ?>
        <table>
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Qty</th>
                    <th>Harga</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($keranjangProduk as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['nama']) ?></td>
                        <td><?= $item['qty'] ?></td>
                        <td>Rp<?= number_format($item['harga']) ?></td>
                        <td>Rp<?= number_format($item['subtotal']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr class="total-row">
                    <td colspan="3">Total</td>
                    <td>Rp<?= number_format($total) ?></td>
                </tr>
                <?php if ($isMember): ?>
                <tr class="total-row">
                    <td colspan="3">Diskon Member</td>
                    <td>- Rp<?= number_format($diskon) ?></td>
                </tr>
                <tr class="total-row">
                    <td colspan="3">Total Bayar</td>
                    <td>Rp<?= number_format($total - $diskon) ?></td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <form method="POST" action="proses-checkout.php">
            <input type="hidden" name="total" value="<?= $total ?>">
            <input type="hidden" name="diskon" value="<?= $diskon ?>">
            <button class="btn-checkout" type="submit">Proses Pembayaran</button>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>
