<?php
include '../service/conection.php';

if (!isset($_GET['id_transaksi'])) {
  die("ID Transaksi tidak tersedia.");
}

$id_transaksi = (int) $_GET['id_transaksi'];

// Gunakan prepared statement untuk keamanan
$stmt = $conn->prepare("
  SELECT t.*, a.username AS admin_nama, m.nama_member, m.poin, m.no_telp, t.fid_member
  FROM transaksi t 
  JOIN admin a ON t.fid_admin = a.id 
  LEFT JOIN member m ON t.fid_member = m.id_member 
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
$query_detail = mysqli_query($conn, "
  SELECT dt.*, p.nama_produk 
  FROM detail_transaksi dt 
  JOIN produk p ON dt.fid_produk = p.id_produk 
  WHERE dt.fid_transaksi = $id_transaksi
");

if (!$query_detail) {
  die("Gagal mengambil detail transaksi.");
}

// Hitung total awal sebelum diskon dari detail transaksi
$query_total_asli = mysqli_query($conn, "
  SELECT SUM(subtotal) AS total_asli FROM detail_transaksi WHERE fid_transaksi = $id_transaksi
");
$data_total = mysqli_fetch_assoc($query_total_asli);
$total_asli = $data_total['total_asli'] ?? 0;

// Hitung diskon dan poin
$total_bayar = $transaksi['total_bayar'];
$kelipatan_500k = floor($total_bayar / 500000);
$diskon_aktual = $kelipatan_500k * 50000;
$diskon = $transaksi['diskon'] !== null ? $transaksi['diskon'] : $diskon_aktual;
$poin_ditambahkan = $kelipatan_500k * 10;
$total_poin_sekarang = $transaksi['poin'] ?? 0;

// Fungsi bantu rupiah
function format_rp($angka) {
  return 'Rp' . number_format($angka, 0, ',', '.');
}

// Siapkan data WhatsApp
$link_wa = '';
if ($transaksi['fid_member']) {
  $nama = $transaksi['nama_member'];
  $no_telp = preg_replace('/[^0-9]/', '', $transaksi['no_telp']);
  $no_wa = (preg_match('/^0[0-9]{9,14}$/', $no_telp)) ? '62' . substr($no_telp, 1) : $no_telp;

  $pesan_lines = [];
  $pesan_lines[] = "===== STRUK BELANJA =====";
  $pesan_lines[] = "ID Transaksi: {$transaksi['id_transaksi']}";
  $pesan_lines[] = "Tanggal    : {$transaksi['tgl_pembelian']}";
  $pesan_lines[] = "Admin      : {$transaksi['admin_nama']}";
  $pesan_lines[] = "Member     : {$nama}";
  $pesan_lines[] = "-------------------------";
  $pesan_lines[] = "Nama Produk       Qty   Harga   Subtotal";

  mysqli_data_seek($query_detail, 0);
  while ($row = mysqli_fetch_assoc($query_detail)) {
    $nama_produk = substr($row['nama_produk'], 0, 15);
    $qty = $row['qty'];
    $harga = number_format($row['harga'], 0, ',', '.');
    $subtotal = number_format($row['subtotal'], 0, ',', '.');

    $line = str_pad($nama_produk, 15, " ", STR_PAD_RIGHT)
          . str_pad($qty, 5, " ", STR_PAD_LEFT)
          . str_pad("Rp".$harga, 10, " ", STR_PAD_LEFT)
          . str_pad("Rp".$subtotal, 12, " ", STR_PAD_LEFT);
    $pesan_lines[] = $line;
  }

  $pesan_lines[] = "-------------------------";
  $pesan_lines[] = "Total Sebelum Diskon: " . format_rp($total_asli);
  $pesan_lines[] = "Diskon Member      : -" . format_rp($diskon);
  $pesan_lines[] = "Total Dibayar      : " . format_rp($total_bayar);
  $pesan_lines[] = "Uang Dibayar       : " . format_rp($transaksi['uang_dibayar']);
  $pesan_lines[] = "Kembalian          : " . format_rp($transaksi['kembalian']);
  $pesan_lines[] = "-------------------------";
  $pesan_lines[] = "Poin Ditambahkan   : $poin_ditambahkan";
  $pesan_lines[] = "Total Poin Saat Ini: $total_poin_sekarang";
  $pesan_lines[] = "===== TERIMA KASIH =====";

  $pesan = implode("%0A", array_map('urlencode', $pesan_lines));
  $link_wa = "https://wa.me/$no_wa?text=$pesan";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <title>Struk Transaksi</title>
  <style>
    body {
      max-width: 360px; /* Lebar struk kasir umum */
      margin: auto;
      font-family: 'Courier New', Courier, monospace;
      font-size: 13px;
      background: white;
      padding: 20px;
      color: black;
      user-select: text;
    }
    h2 {
      text-align: center;
      margin-bottom: 10px;
      font-weight: 700;
      letter-spacing: 1.5px;
      border-bottom: 2px dashed black;
      padding-bottom: 8px;
      user-select: none;
    }
    .info, .footer {
      margin-bottom: 10px;
    }
    .info div {
      margin: 4px 0;
    }
    .produk-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 15px;
    }
    .produk-table thead tr {
      border-bottom: 1px dashed black;
    }
    .produk-table thead th {
      text-align: left;
      padding-bottom: 4px;
    }
    .produk-table tbody td {
      padding: 2px 0;
    }
    .produk-table tbody tr:last-child td {
      border-bottom: 1px dashed black;
    }
    .total-line {
      display: flex;
      justify-content: space-between;
      font-weight: 700;
      margin-bottom: 4px;
    }
    .btn-group {
      margin-top: 20px;
      display: flex;
      justify-content: center;
      gap: 10px;
    }
    button, a.back-button {
      padding: 8px 20px;
      font-size: 14px;
      border-radius: 20px;
      border: none;
      cursor: pointer;
      user-select: none;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: #27ae60;
      color: white;
      font-weight: 600;
      box-shadow: 0 3px 6px rgba(39, 174, 96, 0.5);
      transition: background-color 0.3s ease;
    }
    button:hover, a.back-button:hover {
      background: #1e8449;
      color: #fff;
    }
    a.back-button {
      background: #bdc3c7;
      color: #2c3e50;
      box-shadow: none;
      font-weight: 500;
    }
    a.back-button:hover {
      background: #95a5a6;
      color: white;
    }
    
    @media print {
  body {
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    height: 100vh;
    background: white;
  }

  .btn-group {
    display: none !important;
  }

  .struk-container {
    width: 80mm; /* Untuk printer thermal 80mm */
    max-width: 100%;
    font-size: 13px;
    color: black;
    background: white;
  }

  @page {
    margin: 0;
    size: auto;
  }
}


  </style>
</head>
<body>

<body>
<div class="struk-container">

  <h2>STRUK TRANSAKSI</h2>

  <div class="info">
    <div>ID Transaksi: <?= htmlspecialchars($transaksi['id_transaksi']) ?></div>
    <div>Tanggal    : <?= htmlspecialchars($transaksi['tgl_pembelian']) ?></div>
    <div>Admin      : <?= htmlspecialchars($transaksi['admin_nama']) ?></div>
    <div>Member     : <?= htmlspecialchars($transaksi['nama_member'] ?? '-') ?></div>
    <?php if ($transaksi['no_telp']) : ?>
      <div>No. Telp   : <?= htmlspecialchars($transaksi['no_telp']) ?></div>
    <?php endif; ?>
  </div>

  <table class="produk-table">
    <thead>
      <tr>
        <th>Nama Produk</th>
        <th style="text-align:right;">Qty</th>
        <th style="text-align:right;">Harga</th>
        <th style="text-align:right;">Subtotal</th>
      </tr>
    </thead>
    <tbody>
      <?php 
      mysqli_data_seek($query_detail, 0);
      while($row = mysqli_fetch_assoc($query_detail)) { ?>
      <tr>
        <td><?= htmlspecialchars(substr($row['nama_produk'], 0, 15)) ?></td>
        <td style="text-align:right;"><?= htmlspecialchars($row['qty']) ?></td>
        <td style="text-align:right;">Rp<?= number_format($row['harga'], 0, ',', '.') ?></td>
        <td style="text-align:right;">Rp<?= number_format($row['subtotal'], 0, ',', '.') ?></td>
      </tr>
      <?php } ?>
    </tbody>
  </table>

  <div class="total-line"><div>Total Sebelum Diskon</div><div>Rp<?= number_format($total_asli, 0, ',', '.') ?></div></div>
  <div class="total-line"><div>Diskon Member</div><div>-Rp<?= number_format($diskon, 0, ',', '.') ?></div></div>
  <div class="total-line"><div>Total Dibayar</div><div>Rp<?= number_format($total_bayar, 0, ',', '.') ?></div></div>
  <div class="total-line"><div>Uang Dibayar</div><div>Rp<?= number_format($transaksi['uang_dibayar'], 0, ',', '.') ?></div></div>
  <div class="total-line"><div>Kembalian</div><div>Rp<?= number_format($transaksi['kembalian'], 0, ',', '.') ?></div></div>

  <?php if ($transaksi['fid_member']) : ?>
    <div class="total-line" style="margin-top:15px;"><div>Poin Ditambahkan</div><div><?= $poin_ditambahkan ?> poin</div></div>
    <div class="total-line"><div>Total Poin Saat Ini</div><div><?= $total_poin_sekarang ?> poin</div></div>
  <?php endif; ?>

  <div class="btn-group">
    <button onclick="window.print()">🖨️ Cetak Struk</button>
    <a href="../Scanner/scan.php" class="back-button">🔙 Kembali Belanja</a>
    <?php if ($transaksi['fid_member']) : ?>
      <button onclick="kirimWA()">📩 Kirim WhatsApp</button>
    <?php endif; ?>
  </div>

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
