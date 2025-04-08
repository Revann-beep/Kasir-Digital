<?php
include '../service/conection.php';

// Ambil ID transaksi dari URL
$id_transaksi = $_GET['id_transaksi'];

// Ambil data transaksi + admin + member
$query_transaksi = mysqli_query($conn, "
    SELECT t.*, a.username AS admin_nama, m.nama_member 
    FROM transaksi t 
    JOIN admin a ON t.fid_admin = a.id 
    LEFT JOIN member m ON t.fid_member = m.id_member 
    WHERE t.id_transaksi = $id_transaksi
");
$transaksi = mysqli_fetch_assoc($query_transaksi);

// Ambil data detail transaksi
$query_detail = mysqli_query($conn, "
    SELECT dt.*, p.nama_produk 
    FROM detail_transaksi dt 
    JOIN produk p ON dt.fid_produk = p.id_produk 
    WHERE dt.fid_transaksi = $id_transaksi
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Invoice Transaksi</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        h2 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        td, th { border: 1px solid #ccc; padding: 8px; text-align: center; }
        .no-border { border: none; }
        .print-btn { margin-top: 20px; text-align: center; }
    </style>
</head>
<body>
    <h2>INVOICE TRANSAKSI</h2>

    <table class="no-border">
        <tr><td><strong>ID Transaksi:</strong></td><td><?= $transaksi['id_transaksi'] ?></td></tr>
        <tr><td><strong>Tanggal:</strong></td><td><?= $transaksi['tgl_pembelian'] ?></td></tr>
        <tr><td><strong>Admin:</strong></td><td><?= $transaksi['admin_nama'] ?></td></tr>
        <tr><td><strong>Member:</strong></td><td><?= $transaksi['nama_member'] ?? '-' ?></td></tr>
    </table>

    <h3>Detail Produk</h3>
    <table>
        <tr>
            <th>No</th>
            <th>Nama Produk</th>
            <th>Qty</th>
            <th>Harga</th>
            <th>Subtotal</th>
        </tr>
        <?php 
        $no = 1;
        while($row = mysqli_fetch_assoc($query_detail)) { ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= $row['nama_produk'] ?></td>
                <td><?= $row['qty'] ?></td>
                <td>Rp<?= number_format($row['harga'], 0, ',', '.') ?></td>
                <td>Rp<?= number_format($row['subtotal'], 0, ',', '.') ?></td>
            </tr>
        <?php } ?>
        <tr>
            <td colspan="4"><strong>Total</strong></td>
            <td><strong>Rp<?= number_format($transaksi['total_harga'], 0, ',', '.') ?></strong></td>
        </tr>
    </table>

    <div class="print-btn">
        <button onclick="window.print()">🖨️ Cetak Invoice</button>
    </div>
</body>
</html>
