<?php
include '../service/conection.php';

if (!isset($_GET['id_transaksi'])) {
  die("ID Transaksi tidak tersedia.");
}

$id_transaksi = (int) $_GET['id_transaksi'];

// Ambil data transaksi
$query_transaksi = mysqli_query($conn, "
  SELECT t.*, a.username AS admin_nama, m.nama_member, m.poin, m.no_telp 
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
$diskon = $transaksi['diskon'] ?? ($total_asli - $transaksi['total_harga']);

$poin_ditambahkan = floor($transaksi['total_harga'] / 1000);
$total_poin_sekarang = isset($transaksi['poin']) ? $transaksi['poin'] : 0;

// Siapkan data WA jika member
$link_wa = '';
if ($transaksi['fid_member']) {
  $nama = urlencode($transaksi['nama_member']);
  $no_telp = preg_replace('/[^0-9]/', '', $transaksi['no_telp']);
  if (substr($no_telp, 0, 1) == '0') {
    $no_wa = '62' . substr($no_telp, 1);
  } else {
    $no_wa = $no_telp;
  }
  $total = number_format($transaksi['total_harga'], 0, ',', '.');
  $poin = $poin_ditambahkan;
  $pesan = urlencode("Hai $nama, terima kasih sudah berbelanja. Total: Rp$total. Poin ditambahkan: $poin.");
  $link_wa = "https://wa.me/$no_wa?text=$pesan";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <title>Invoice Transaksi</title>
  <style>
    /* CSS dipersingkat di sini agar fokus ke fungsi. Gunakan dari versi sebelumnya. */
       /* Reset & base */
    * {
      box-sizing: border-box;
    }
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      padding: 30px;
      font-size: 14px;
      background: #f0f2f5;
      color: #2c3e50;
      max-width: 900px;
      margin: auto;
      line-height: 1.5;
    }

    h2 {
      text-align: center;
      font-size: 28px;
      margin-bottom: 30px;
      font-weight: 700;
      color: #27ae60;
      letter-spacing: 1px;
      text-transform: uppercase;
      user-select: none;
    }

    .invoice-info {
      display: flex;
      justify-content: space-between;
      background: white;
      padding: 20px 30px;
      border-radius: 10px;
      box-shadow: 0 4px 15px rgb(0 0 0 / 0.1);
      margin-bottom: 40px;
      flex-wrap: wrap;
      gap: 20px;
    }

    .invoice-info > div {
      flex: 1 1 45%;
      min-width: 250px;
    }

    .invoice-info strong {
      display: block;
      margin-bottom: 6px;
      color: #34495e;
      font-weight: 600;
      font-size: 13px;
      letter-spacing: 0.03em;
    }

    .invoice-info span {
      display: block;
      font-size: 15px;
      font-weight: 700;
      color: #2c3e50;
      margin-bottom: 16px;
      user-select: text;
    }

    h3 {
      font-size: 20px;
      color: #27ae60;
      margin-bottom: 15px;
      border-bottom: 3px solid #27ae60;
      padding-bottom: 8px;
      font-weight: 700;
      user-select: none;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      background: white;
      box-shadow: 0 4px 10px rgb(0 0 0 / 0.05);
      border-radius: 10px;
      overflow: hidden;
      font-size: 14px;
      margin-bottom: 40px;
    }

    thead tr {
      background-color: #27ae60;
      color: white;
      text-transform: uppercase;
      font-weight: 700;
      user-select: none;
    }

    th, td {
      padding: 14px 18px;
      text-align: left;
      border-bottom: 1px solid #ecf0f1;
    }

    tbody tr:hover {
      background-color: #ecf9f1;
    }

    tbody tr:last-child td {
      border-bottom: none;
    }

    .total-row td {
      font-weight: 700;
      background-color: #f9f9f9;
      color: #2c3e50;
      font-size: 15px;
    }

    .total-row td:first-child {
      text-align: right;
      font-style: italic;
    }

    /* Poin member table */
    .poin-table {
      width: 300px;
      border: none;
      margin-bottom: 50px;
    }

    .poin-table td {
      padding: 10px 8px;
      border: none;
      font-size: 15px;
      font-weight: 600;
      color: #27ae60;
      user-select: text;
    }

    .poin-table td:first-child {
      font-weight: 400;
      color: #34495e;
      width: 160px;
    }

    /* Button group */
    .btn-group {
      display: flex;
      justify-content: center;
      gap: 15px;
      margin-bottom: 40px;
    }

    button, .back-button {
      font-size: 14px;
      padding: 12px 30px;
      border: none;
      border-radius: 30px;
      cursor: pointer;
      transition: background-color 0.3s ease, color 0.3s ease;
      user-select: none;
      box-shadow: 0 4px 8px rgb(39 174 96 / 0.35);
      font-weight: 600;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }

    button {
      background-color: #27ae60;
      color: white;
    }

    button:hover {
      background-color: #1e8449;
    }

    .back-button {
      background-color: #bdc3c7;
      color: #2c3e50;
      border: 1px solid transparent;
    }

    .back-button:hover {
      background-color: #95a5a6;
      color: white;
      border-color: #27ae60;
    }

    /* Print style */
    @media print {
      body {
        background: white;
        padding: 0;
      }
      .btn-group {
        display: none;
      }
      h2 {
        color: #2c3e50;
      }
      .invoice-info, table, .poin-table {
        box-shadow: none;
        border-radius: 0;
      }
    }
  </style>
</head>
<body>

  <h2>INVOICE TRANSAKSI</h2>

  <div class="invoice-info">
    <div>
      <strong>ID Transaksi</strong>
      <span><?= htmlspecialchars($transaksi['id_transaksi']) ?></span>
      <strong>Tanggal</strong>
      <span><?= htmlspecialchars($transaksi['tgl_pembelian']) ?></span>
      <strong>Metode Pembayaran</strong>
      <span><?= htmlspecialchars($transaksi['metode_pembayaran'] ?? 'Tunai') ?></span>
    </div>
    <div>
      <strong>Admin</strong>
      <span><?= htmlspecialchars($transaksi['admin_nama']) ?></span>
      <strong>Member</strong>
      <span><?= htmlspecialchars($transaksi['nama_member'] ?? '-') ?></span>
      <?php if ($transaksi['no_telp']) : ?>
        <strong>No. Telepon</strong>
        <span><?= htmlspecialchars($transaksi['no_telp']) ?></span>
      <?php endif; ?>
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
        <td><?= htmlspecialchars($row['nama_produk']) ?></td>
        <td><?= htmlspecialchars($row['qty']) ?></td>
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
    <table class="poin-table">
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

  <div class="btn-group">
    <button onclick="window.print()">🖨️ Cetak Invoice</button>
    <a href="../Scanner/scan.php" class="back-button">🔙 Kembali Belanja</a>
    <?php if ($transaksi['fid_member']) : ?>
      <button onclick="kirimWA()">📩 Kirim WhatsApp</button>
    <?php endif; ?>
  </div>

  <?php if ($transaksi['fid_member']) : ?>
    <script>
      function kirimWA() {
        if (confirm("Kirim pesan WhatsApp ke member?")) {
          window.open("<?= $link_wa ?>", "_blank");
        }
      }
    </script>
  <?php endif; ?>

</body>
</html>
