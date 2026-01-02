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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Struk Transaksi #<?= $id_transaksi ?></title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
    }

    body {
      font-family: 'Courier New', 'Monaco', monospace;
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
      color: #212529;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }

    .container {
      max-width: 500px;
      width: 100%;
    }

    .struk-wrapper {
      background: white;
      border-radius: 16px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
      overflow: hidden;
      margin-bottom: 30px;
      border: 2px solid #e0e0e0;
      transition: transform 0.3s ease;
    }

    .struk-wrapper:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 50px rgba(0, 0, 0, 0.2);
    }

    .struk-header {
      background: linear-gradient(135deg, #2c3e50 0%, #4a6583 100%);
      color: white;
      padding: 25px 30px;
      text-align: center;
      position: relative;
      overflow: hidden;
    }

    .struk-header::before {
      content: "";
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
      background-size: 20px 20px;
      opacity: 0.3;
    }

    .struk-header h1 {
      font-size: 28px;
      font-weight: 700;
      letter-spacing: 3px;
      margin-bottom: 8px;
      position: relative;
      text-transform: uppercase;
      text-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }

    .struk-subtitle {
      font-size: 14px;
      opacity: 0.9;
      letter-spacing: 1px;
    }

    .struk-id {
      position: absolute;
      top: 15px;
      right: 15px;
      background: rgba(255,255,255,0.2);
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 13px;
      font-weight: bold;
      backdrop-filter: blur(10px);
    }

    .struk-body {
      padding: 30px;
    }

    .info-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 15px;
      margin-bottom: 25px;
      padding-bottom: 20px;
      border-bottom: 2px dashed #e0e0e0;
    }

    .info-item {
      display: flex;
      flex-direction: column;
      gap: 5px;
    }

    .info-label {
      font-size: 12px;
      color: #6c757d;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      font-weight: 600;
    }

    .info-value {
      font-size: 14px;
      font-weight: 600;
      color: #212529;
    }

    .member-badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
      color: white;
      padding: 6px 15px;
      border-radius: 20px;
      font-size: 13px;
      font-weight: 600;
      margin-top: 5px;
    }

    .member-badge i {
      font-size: 12px;
    }

    .produk-header {
      background: #f8f9fa;
      padding: 12px 15px;
      border-radius: 10px;
      margin-bottom: 15px;
      border: 1px solid #e0e0e0;
    }

    .produk-header h3 {
      font-size: 16px;
      color: #2c3e50;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .produk-header h3 i {
      color: #27ae60;
    }

    .produk-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 25px;
    }

    .produk-table th {
      text-align: left;
      padding: 12px 10px;
      font-size: 13px;
      color: #6c757d;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      border-bottom: 2px solid #e0e0e0;
      font-weight: 600;
    }

    .produk-table td {
      padding: 12px 10px;
      border-bottom: 1px solid #f0f0f0;
      font-size: 14px;
    }

    .produk-table tr:hover td {
      background-color: #f8f9fa;
    }

    .produk-table td:first-child {
      font-weight: 600;
      color: #2c3e50;
    }

    .produk-table td:nth-child(2),
    .produk-table td:nth-child(3),
    .produk-table td:nth-child(4) {
      text-align: right;
      font-family: 'Courier New', monospace;
      font-weight: 600;
    }

    .summary-section {
      background: #f8f9fa;
      border-radius: 12px;
      padding: 20px;
      margin-top: 25px;
      border: 1px solid #e0e0e0;
    }

    .summary-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 10px 0;
      border-bottom: 1px dashed #dee2e6;
    }

    .summary-row:last-child {
      border-bottom: none;
      border-top: 2px solid #dee2e6;
      margin-top: 10px;
      padding-top: 15px;
      font-size: 18px;
      font-weight: 700;
      color: #2c3e50;
    }

    .summary-label {
      font-size: 14px;
      color: #6c757d;
      font-weight: 500;
    }

    .summary-value {
      font-size: 15px;
      font-weight: 600;
      color: #212529;
      font-family: 'Courier New', monospace;
    }

    .poin-section {
      background: linear-gradient(135deg, #ffd166 0%, #ffcc00 100%);
      border-radius: 12px;
      padding: 20px;
      margin-top: 20px;
      border: 1px solid #ffc107;
    }

    .poin-title {
      display: flex;
      align-items: center;
      gap: 10px;
      color: #856404;
      font-size: 16px;
      font-weight: 600;
      margin-bottom: 15px;
    }

    .poin-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 15px;
    }

    .poin-item {
      text-align: center;
      padding: 15px;
      background: rgba(255, 255, 255, 0.9);
      border-radius: 8px;
      border: 1px solid rgba(255, 193, 7, 0.3);
    }

    .poin-value {
      font-size: 24px;
      font-weight: 700;
      color: #856404;
      margin-bottom: 5px;
    }

    .poin-label {
      font-size: 12px;
      color: #b38b00;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      font-weight: 600;
    }

    .action-buttons {
      display: flex;
      gap: 15px;
      margin-top: 30px;
      flex-wrap: wrap;
    }

    .action-btn {
      flex: 1;
      min-width: 200px;
      padding: 16px 24px;
      border-radius: 12px;
      border: none;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      text-decoration: none;
      text-align: center;
    }

    .btn-print {
      background: linear-gradient(135deg, #4361ee 0%, #3a56d4 100%);
      color: white;
      box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
    }

    .btn-print:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 20px rgba(67, 97, 238, 0.4);
      background: linear-gradient(135deg, #3a56d4 0%, #2f46b5 100%);
    }

    .btn-whatsapp {
      background: linear-gradient(135deg, #25d366 0%, #1da851 100%);
      color: white;
      box-shadow: 0 4px 15px rgba(37, 211, 102, 0.3);
    }

    .btn-whatsapp:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 20px rgba(37, 211, 102, 0.4);
      background: linear-gradient(135deg, #1da851 0%, #168a3d 100%);
    }

    .btn-back {
      background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
      color: white;
      box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
    }

    .btn-back:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 20px rgba(108, 117, 125, 0.4);
      background: linear-gradient(135deg, #5a6268 0%, #495057 100%);
    }

    /* Print Styles */
    @media print {
      body {
        background: white !important;
        padding: 0 !important;
        margin: 0 !important;
        display: block !important;
      }

      .container {
        max-width: 100% !important;
        width: 100% !important;
      }

      .struk-wrapper {
        box-shadow: none !important;
        border: none !important;
        border-radius: 0 !important;
        margin: 0 !important;
      }

      .struk-header {
        background: #2c3e50 !important;
        -webkit-print-color-adjust: exact;
      }

      .action-buttons {
        display: none !important;
      }

      .member-badge {
        background: #27ae60 !important;
        -webkit-print-color-adjust: exact;
      }

      .poin-section {
        background: #ffd166 !important;
        -webkit-print-color-adjust: exact;
      }

      .summary-section {
        background: #f8f9fa !important;
        -webkit-print-color-adjust: exact;
      }

      .produk-header {
        background: #f8f9fa !important;
        -webkit-print-color-adjust: exact;
      }

      .btn-print,
      .btn-whatsapp,
      .btn-back {
        display: none !important;
      }
    }

    /* Responsive */
    @media (max-width: 576px) {
      .struk-body {
        padding: 20px;
      }

      .info-grid {
        grid-template-columns: 1fr;
        gap: 10px;
      }

      .action-buttons {
        flex-direction: column;
      }

      .action-btn {
        min-width: 100%;
      }

      .poin-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="struk-wrapper">
      <!-- Header Struk -->
      <div class="struk-header">
        <span class="struk-id">#<?= $id_transaksi ?></span>
        <h1>STRUK TRANSAKSI</h1>
        <div class="struk-subtitle">TOKO RETAIL TERPERCAYA</div>
      </div>

      <!-- Body Struk -->
      <div class="struk-body">
        <!-- Informasi Transaksi -->
        <div class="info-grid">
          <div class="info-item">
            <span class="info-label">Tanggal Transaksi</span>
            <span class="info-value"><?= htmlspecialchars($transaksi['tgl_pembelian']) ?></span>
          </div>
          <div class="info-item">
            <span class="info-label">Kasir / Admin</span>
            <span class="info-value"><?= htmlspecialchars($transaksi['admin_nama']) ?></span>
          </div>
          <div class="info-item">
            <span class="info-label">Metode Pembayaran</span>
            <span class="info-value">Tunai</span>
          </div>
          <div class="info-item">
            <span class="info-label">Status</span>
            <span class="info-value" style="color: #27ae60; font-weight: 700;">LUNAS</span>
          </div>
        </div>

        <!-- Informasi Member -->
        <?php if ($transaksi['fid_member']): ?>
          <div class="info-item" style="margin-bottom: 25px;">
            <span class="info-label">Member</span>
            <span class="info-value"><?= htmlspecialchars($transaksi['nama_member']) ?></span>
            <?php if ($transaksi['no_telp']): ?>
              <span class="info-value" style="font-size: 13px; color: #6c757d;">
                <i class="fas fa-phone"></i> <?= htmlspecialchars($transaksi['no_telp']) ?>
              </span>
            <?php endif; ?>
            <span class="member-badge">
              <i class="fas fa-crown"></i> Member Aktif
            </span>
          </div>
        <?php endif; ?>

        <!-- Daftar Produk -->
        <div class="produk-header">
          <h3><i class="fas fa-shopping-basket"></i> DAFTAR PRODUK</h3>
        </div>

        <table class="produk-table">
          <thead>
            <tr>
              <th>Nama Produk</th>
              <th style="text-align: right;">Qty</th>
              <th style="text-align: right;">Harga</th>
              <th style="text-align: right;">Subtotal</th>
            </tr>
          </thead>
          <tbody>
            <?php 
            mysqli_data_seek($query_detail, 0);
            while($row = mysqli_fetch_assoc($query_detail)) { 
            ?>
            <tr>
              <td><?= htmlspecialchars($row['nama_produk']) ?></td>
              <td><?= htmlspecialchars($row['qty']) ?></td>
              <td>Rp<?= number_format($row['harga'], 0, ',', '.') ?></td>
              <td>Rp<?= number_format($row['subtotal'], 0, ',', '.') ?></td>
            </tr>
            <?php } ?>
          </tbody>
        </table>

        <!-- Ringkasan Pembayaran -->
        <div class="summary-section">
          <div class="summary-row">
            <span class="summary-label">Total Sebelum Diskon</span>
            <span class="summary-value">Rp<?= number_format($total_asli, 0, ',', '.') ?></span>
          </div>
          
          <?php if ($diskon > 0): ?>
          <div class="summary-row" style="color: #27ae60;">
            <span class="summary-label">Diskon Member</span>
            <span class="summary-value">-Rp<?= number_format($diskon, 0, ',', '.') ?></span>
          </div>
          <?php endif; ?>
          
          <div class="summary-row">
            <span class="summary-label">Uang Dibayar</span>
            <span class="summary-value">Rp<?= number_format($transaksi['uang_dibayar'], 0, ',', '.') ?></span>
          </div>
          
          <div class="summary-row">
            <span class="summary-label">Kembalian</span>
            <span class="summary-value">Rp<?= number_format($transaksi['kembalian'], 0, ',', '.') ?></span>
          </div>
          
          <div class="summary-row">
            <span class="summary-label">TOTAL BAYAR</span>
            <span class="summary-value">Rp<?= number_format($total_bayar, 0, ',', '.') ?></span>
          </div>
        </div>

        <!-- Poin Section -->
        <?php if ($transaksi['fid_member']): ?>
          <div class="poin-section">
            <div class="poin-title">
              <i class="fas fa-coins"></i>
              <span>POIN & REWARDS</span>
            </div>
            <div class="poin-grid">
              <div class="poin-item">
                <div class="poin-value"><?= $poin_ditambahkan ?></div>
                <div class="poin-label">Poin Ditambahkan</div>
              </div>
              <div class="poin-item">
                <div class="poin-value"><?= $total_poin_sekarang ?></div>
                <div class="poin-label">Total Poin Saat Ini</div>
              </div>
            </div>
          </div>
        <?php endif; ?>

        <!-- Footer Struk -->
        <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 2px dashed #e0e0e0;">
          <p style="font-size: 13px; color: #6c757d; margin-bottom: 10px;">
            <i class="fas fa-shield-alt"></i> Transaksi ini telah tercatat secara elektronik
          </p>
          <p style="font-size: 12px; color: #6c757d; font-weight: 600;">
            TERIMA KASIH ATAS KEPERCAYAAN ANDA
          </p>
        </div>
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="action-buttons">
      <button onclick="window.print()" class="action-btn btn-print">
        <i class="fas fa-print"></i> Cetak Struk
      </button>
      
      <?php if ($transaksi['fid_member']): ?>
        <button onclick="kirimWA()" class="action-btn btn-whatsapp">
          <i class="fab fa-whatsapp"></i> Kirim ke WhatsApp
        </button>
      <?php endif; ?>
      
      <a href="../admin/produk.php" class="action-btn btn-back">
        <i class="fas fa-arrow-left"></i> Kembali ke Produk
      </a>
    </div>
  </div>

  <?php if ($transaksi['fid_member']): ?>
  <script>
    function kirimWA() {
      if (confirm("Kirim struk transaksi ke WhatsApp member?")) {
        window.open("<?= $link_wa ?>", "_blank");
      }
    }
  </script>
  <?php endif; ?>

  <script>
    // Optimasi untuk print
    document.addEventListener('DOMContentLoaded', function() {
      const printBtn = document.querySelector('.btn-print');
      if (printBtn) {
        printBtn.addEventListener('click', function() {
          setTimeout(function() {
            window.print();
          }, 100);
        });
      }
    });
  </script>
</body>
</html>