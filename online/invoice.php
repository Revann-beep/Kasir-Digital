<?php
include '../service/conection.php';

if (!isset($_GET['id_transaksi'])) {
    die("ID Transaksi tidak tersedia.");
}

$id_transaksi = (int) $_GET['id_transaksi'];

// Ambil data transaksi
$stmt = $conn->prepare("
    SELECT t.*, a.username AS nama_admin 
    FROM transaksi t
    LEFT JOIN admin a ON t.fid_admin = a.id
    WHERE t.id_transaksi = ?
");
$stmt->bind_param("i", $id_transaksi);
$stmt->execute();
$result = $stmt->get_result();
$transaksi = $result->fetch_assoc();

if (!$transaksi) {
    die("Transaksi tidak ditemukan.");
}

// Ambil detail produk
$stmtDetail = $conn->prepare("
    SELECT dt.*, p.nama_produk 
    FROM detail_transaksi dt
    JOIN produk p ON dt.fid_produk = p.id_produk
    WHERE dt.fid_transaksi = ?
");
$stmtDetail->bind_param("i", $id_transaksi);
$stmtDetail->execute();
$detailResult = $stmtDetail->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk Transaksi</title>
    <style>
        body {
            font-family: monospace;
            font-size: 12px;
            width: 250px;
            margin: auto;
        }
        h2, p {
            text-align: center;
            margin: 0;
            padding: 4px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }
        td {
            padding: 2px 0;
        }
        .total {
            font-weight: bold;
            border-top: 1px dashed #000;
            margin-top: 5px;
        }
        .footer {
            text-align: center;
            margin-top: 10px;
        }
        @media print {
            body {
                margin: 0;
            }
        }
    </style>
</head>
<body onload="window.print()">

<h2>TOKO ABC</h2>
<p>Jl. Contoh No.123</p>
<p><?= date("d/m/Y H:i", strtotime($transaksi['tgl_pembelian'])) ?></p>
<p>Kasir: <?= $transaksi['nama_admin'] ?></p>
<hr>

<table>
    <?php while ($item = $detailResult->fetch_assoc()): ?>
        <tr>
            <td colspan="2"><?= $item['nama_produk'] ?></td>
        </tr>
        <tr>
            <td><?= $item['qty'] ?> x Rp<?= number_format($item['harga'], 0, ',', '.') ?></td>
            <td style="text-align:right;">Rp<?= number_format($item['subtotal'], 0, ',', '.') ?></td>
        </tr>
    <?php endwhile; ?>
</table>

<hr>
<table>
    <tr>
        <td><strong>Total</strong></td>
        <td style="text-align:right;">Rp<?= number_format($transaksi['total_harga'], 0, ',', '.') ?></td>
    </tr>
    <tr>
        <td><strong>Diskon</strong></td>
        <td style="text-align:right;">Rp<?= number_format($transaksi['diskon'], 0, ',', '.') ?></td>
    </tr>
    <tr>
        <td><strong>Total Bayar</strong></td>
        <td style="text-align:right;">Rp<?= number_format($transaksi['total_bayar'], 0, ',', '.') ?></td>
    </tr>
</table>

<div class="footer">
    <p>Terima Kasih!</p>
    <p>Barang yang sudah dibeli tidak dapat dikembalikan</p>
</div>

</body>
</html>
