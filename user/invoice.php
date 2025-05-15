<?php
include '../service/conection.php';

if (!isset($_GET['id_transaksi'])) {
  die("ID Transaksi tidak tersedia.");
}

$id_transaksi = (int) $_GET['id_transaksi'];

// Ambil data transaksi
$query_transaksi = mysqli_query($conn, "
  SELECT t.*, a.username AS admin_nama, m.nama_member, m.poin 
  FROM transaksi t 
  JOIN admin a ON t.fid_admin = a.id 
  LEFT JOIN member m ON t.fid_member = m.id_member 
  WHERE t.id_transaksi = $id_transaksi
");
$transaksi = mysqli_fetch_assoc($query_transaksi);

// Ambil detail produk
$query_detail = mysqli_query($conn, "
  SELECT dt.*, p.nama_produk 
  FROM detail_transaksi dt 
  JOIN produk p ON dt.fid_produk = p.id_produk 
  WHERE dt.fid_transaksi = $id_transaksi
");

// Hitung total awal sebelum diskon
$query_total_asli = mysqli_query($conn, "
  SELECT SUM(subtotal) AS total_asli FROM detail_transaksi WHERE fid_transaksi = $id_transaksi
");
$data_total = mysqli_fetch_assoc($query_total_asli);
$total_asli = $data_total['total_asli'];
$diskon = $transaksi['diskon'] ?? ($total_asli - $transaksi['total_harga']); // fallback kalau diskon null

// Hitung poin
$poin_ditambahkan = floor($transaksi['total_harga'] / 1000);
$total_poin_sekarang = isset($transaksi['poin']) ? $transaksi['poin'] : 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Invoice Transaksi</title>
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      padding: 10px;
      font-size: 13px;
      background: #f9f9f9;
      color: #333;
      max-width: 900px;
      margin: auto;
    }
    h2 {
      text-align: center;
      font-size: 22px;
      margin-bottom: 20px;
      color: #2c3e50;
    }
    .invoice-info {
      display: flex;
      justify-content: space-between;
      margin-bottom: 20px;
      background: #fff;
      padding: 12px;
      border-radius: 6px;
      box-shadow: 0 0 5px rgba(0,0,0,0.05);
    }
    .invoice-info div {
      width: 48%;
    }
    .invoice-info strong {
      display: block;
      margin-bottom: 4px;
      color: #555;
      font-size: 13px;
    }
    .invoice-info span {
      font-weight: 600;
      color: #000;
      margin-bottom: 8px;
      display: block;
      font-size: 13px;
    }
    h3 {
      margin-top: 25px;
      font-size: 16px;
      color: #2c3e50;
      border-bottom: 1px solid #ccc;
      padding-bottom: 4px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
      background: #fff;
      box-shadow: 0 0 5px rgba(0,0,0,0.05);
      font-size: 12px;
    }
    th, td {
      padding: 8px;
      text-align: left;
      border-bottom: 1px solid #eee;
    }
    th {
      background-color: #2ecc71;
      color: white;
      text-transform: uppercase;
      font-size: 11px;
    }
    td {
      font-size: 12px;
    }
    tr:last-child td {
      border-bottom: none;
    }
    .total-row td {
      font-weight: bold;
      background-color: #f0f0f0;
    }
    .print-btn {
      text-align: center;
      margin-top: 20px;
    }
    button {
      font-size: 13px;
      padding: 8px 20px;
      border: none;
      border-radius: 4px;
      background-color: #3498db;
      color: white;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }
    button:hover {
      background-color: #2980b9;
    }
    @media print {
      .print-btn {
        display: none;
      }
      body {
        background: white;
        padding: 5px;
      }
    }
    .back-button {
      display: inline-block;
      margin-left: 10px;
      text-decoration: none;
      background-color: #95a5a6;
      color: white;
      padding: 8px 16px;
      border-radius: 4px;
      font-size: 13px;
      transition: background-color 0.3s ease;
    }
    .back-button:hover {
      background-color: #7f8c8d;
    }
  </style>
</head>
<body>

<h2>INVOICE TRANSAKSI</h2>

<div class="invoice-info">
  <div>
    <strong>ID Transaksi</strong>
    <span><?= $transaksi['id_transaksi'] ?></span>
    <strong>Tanggal</strong>
    <span><?= $transaksi['tgl_pembelian'] ?></span>
    <strong>Metode Pembayaran</strong>
    <span><?= $transaksi['metode_pembayaran'] ?? 'Tunai' ?></span>
  </div>
  <div>
    <strong>Admin</strong>
    <span><?= $transaksi['admin_nama'] ?></span>
    <strong>Member</strong>
    <span><?= $transaksi['nama_member'] ?? '-' ?></span>
  </div>
</div>

<h3>Detail Produk</h3>
<table>
  <thead>
    <tr>
      <th>No</th>
      <th>Nama Produk</th>
      <th>Qty</th>
      <th>Harga</th>
      <th>Subtotal</th>
    </tr>
  </thead>
  <tbody>
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
    <tr class="total-row">
      <td colspan="4">Total Sebelum Diskon</td>
      <td>Rp<?= number_format($total_asli, 0, ',', '.') ?></td>
    </tr>
    <tr class="total-row">
      <td colspan="4">Diskon Member</td>
      <td>-Rp<?= number_format($diskon, 0, ',', '.') ?></td>
    </tr>
    <tr class="total-row">
      <td colspan="4">Total Dibayar</td>
      <td>Rp<?= number_format($transaksi['total_harga'], 0, ',', '.') ?></td>
    </tr>
    <tr class="total-row">
      <td colspan="4">Uang Dibayar</td>
      <td>Rp<?= number_format($transaksi['uang_dibayar'], 0, ',', '.') ?></td>
    </tr>
    <tr class="total-row">
      <td colspan="4">Kembalian</td>
      <td>Rp<?= number_format($transaksi['kembalian'], 0, ',', '.') ?></td>
    </tr>
  </tbody>
</table>

<?php if ($transaksi['fid_member']) : ?>
  <h3>Informasi Poin Member</h3>
  <table>
    <tr>
      <td>Poin Ditambahkan</td>
      <td><?= $poin_ditambahkan ?> poin</td>
    </tr>
    <tr>
      <td>Total Poin Saat Ini</td>
      <td><?= $total_poin_sekarang ?> poin</td>
    </tr>
  </table>
<?php endif; ?>

<div class="print-btn">
  <button onclick="window.print()">🖨️ Cetak Invoice</button>
  <a href="../Scanner/scan.php" class="back-button">🔙 Kembali Belanja</a>
</div>

</body>
</html>
