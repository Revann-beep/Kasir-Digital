<?php
session_start();
include '../service/conection.php';

// Set waktu expired keranjang (1 jam)
$waktu_expired = 60 * 60;
if (isset($_SESSION['keranjang'])) {
    if (!isset($_SESSION['keranjang_waktu'])) {
        $_SESSION['keranjang_waktu'] = time();
    } elseif (time() - $_SESSION['keranjang_waktu'] > $waktu_expired) {
        unset($_SESSION['keranjang']);
        unset($_SESSION['keranjang_waktu']);
        unset($_SESSION['fid_member']);
        echo "<script>alert('Waktu checkout habis (1 jam), keranjang dihapus.'); location.reload();</script>";
        exit;
    }
}

// Reset member jika tombol reset ditekan
if (isset($_POST['reset_member'])) {
    unset($_SESSION['fid_member']);
    header("Location: keranjang.php");
    exit;
}

$fid_member = $_SESSION['fid_member'] ?? null;
$member = null;
$error_msg = null;
$success_msg = null;

// Cari member berdasarkan no_telp
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['no_telp']) && !isset($_POST['reset_member'])) {
    $no_telp = mysqli_real_escape_string($conn, $_POST['no_telp']);
    $q = mysqli_query($conn, "SELECT * FROM member WHERE no_telp='$no_telp'");
    if (mysqli_num_rows($q) > 0) {
        $member = mysqli_fetch_assoc($q);
        
        // Jika member tidak aktif, aktifkan otomatis
        if ($member['status'] !== 'aktif') {
            mysqli_query($conn, "UPDATE member SET status='aktif', tanggal_aktif=NOW() WHERE id_member=" . $member['id_member']);
            $member['status'] = 'aktif'; // Update status di variabel juga
            $success_msg = "Member berhasil diaktifkan!";
        } else {
            $success_msg = "Member ditemukan!";
        }

        $_SESSION['fid_member'] = $member['id_member'];
        header("Location: keranjang.php");
        exit;
    } else {
        $error_msg = "Member tidak ditemukan.";
    }
}

if ($fid_member) {
    $q = mysqli_query($conn, "SELECT * FROM member WHERE id_member=$fid_member");
    $member = mysqli_fetch_assoc($q);
}

// Tangkap dan proses barcode yang dimasukkan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['barcode'])) {
    $barcode = mysqli_real_escape_string($conn, $_POST['barcode']);
    $query = mysqli_query($conn, "SELECT * FROM produk WHERE barcode = '$barcode'");

    if ($query && mysqli_num_rows($query) > 0) {
        $produk = mysqli_fetch_assoc($query);
        $id_produk = $produk['id_produk'];

        // Cek apakah produk sudah ada di keranjang
        if (!isset($_SESSION['keranjang'])) {
            $_SESSION['keranjang'] = [];
        }

        if (isset($_SESSION['keranjang'][$id_produk])) {
            // Tambah qty jika sudah ada
            $_SESSION['keranjang'][$id_produk]['qty'] += 1;
            $success_msg = "Jumlah produk '{$produk['nama_produk']}' ditambah menjadi " . $_SESSION['keranjang'][$id_produk]['qty'];
        } else {
            // Tambah produk baru ke keranjang
            $_SESSION['keranjang'][$id_produk] = [
                'id_produk' => $produk['id_produk'],
                'nama' => $produk['nama_produk'],
                'harga' => $produk['harga_jual'],
                'qty' => 1
            ];
            $success_msg = "Produk '{$produk['nama_produk']}' ditambahkan ke keranjang";
        }

        // Reset waktu keranjang
        $_SESSION['keranjang_waktu'] = time();

        // Redirect untuk mencegah form resubmit
        header("Location: keranjang.php");
        exit;
    } else {
        $error_msg = "Produk dengan barcode '$barcode' tidak ditemukan.";
    }
}

$poin_member = $member ? (int)$member['poin'] : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - POS System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --secondary: #7209b7;
            --success: #4cc9f0;
            --danger: #f72585;
            --warning: #f8961e;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --gray-light: #e9ecef;
            --border-radius: 12px;
            --box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: var(--dark);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .header h1 {
            color: var(--primary);
            font-size: 2.2rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .header h1 i {
            color: var(--secondary);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: var(--border-radius);
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: var(--transition);
            font-size: 1rem;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(67, 97, 238, 0.25);
        }

        .btn-danger {
            background-color: var(--danger);
            color: white;
        }

        .btn-danger:hover {
            background-color: #e1156d;
            transform: translateY(-2px);
        }

        .btn-success {
            background-color: #2ecc71;
            color: white;
        }

        .btn-success:hover {
            background-color: #27ae60;
            transform: translateY(-2px);
        }

        .btn-warning {
            background-color: var(--warning);
            color: white;
        }

        .btn-warning:hover {
            background-color: #e68a19;
        }

        .btn-outline {
            background-color: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
        }

        .btn-outline:hover {
            background-color: var(--primary);
            color: white;
        }

        .card {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 25px;
            margin-bottom: 25px;
            transition: var(--transition);
        }

        .card:hover {
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }

        .card-title {
            font-size: 1.4rem;
            margin-bottom: 20px;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 2px solid var(--gray-light);
            padding-bottom: 10px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark);
        }

        .form-control {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid var(--gray-light);
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }

        .member-info-card {
            background: linear-gradient(135deg, #4361ee, #3a56d4);
            color: white;
            padding: 20px;
            border-radius: var(--border-radius);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .member-details h3 {
            font-size: 1.5rem;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .member-details p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .poin-badge {
            background-color: rgba(255, 255, 255, 0.2);
            padding: 8px 16px;
            border-radius: 50px;
            font-weight: bold;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .alert {
            padding: 16px 20px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .alert-danger {
            background-color: #ffe6e9;
            border-left: 5px solid var(--danger);
            color: #b00020;
        }

        .alert-success {
            background-color: #e8f5e9;
            border-left: 5px solid #2ecc71;
            color: #1b5e20;
        }

        .alert i {
            font-size: 1.2rem;
        }

        .table-container {
            overflow-x: auto;
            border-radius: var(--border-radius);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }

        thead {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
        }

        th {
            padding: 18px 15px;
            text-align: left;
            font-weight: 600;
            font-size: 1rem;
        }

        th:first-child {
            border-radius: var(--border-radius) 0 0 0;
        }

        th:last-child {
            border-radius: 0 var(--border-radius) 0 0;
        }

        td {
            padding: 18px 15px;
            border-bottom: 1px solid var(--gray-light);
        }

        tbody tr {
            transition: var(--transition);
        }

        tbody tr:hover {
            background-color: rgba(67, 97, 238, 0.05);
        }

        .qty-control {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .qty-btn {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--gray-light);
            color: var(--dark);
            text-decoration: none;
            font-weight: bold;
            transition: var(--transition);
        }

        .qty-btn:hover {
            background-color: var(--primary);
            color: white;
            transform: scale(1.1);
        }

        .qty-value {
            font-weight: bold;
            min-width: 30px;
            text-align: center;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            color: white;
            text-decoration: none;
            transition: var(--transition);
        }

        .action-btn:hover {
            transform: scale(1.1);
        }

        .btn-delete {
            background-color: var(--danger);
        }

        .btn-delete:hover {
            background-color: #e1156d;
        }

        .checkbox-container {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .checkbox-custom {
            width: 22px;
            height: 22px;
            border-radius: 6px;
            border: 2px solid var(--gray);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }

        input[type="checkbox"] {
            display: none;
        }

        input[type="checkbox"]:checked + .checkbox-custom {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        input[type="checkbox"]:checked + .checkbox-custom::after {
            content: "✓";
            color: white;
            font-weight: bold;
            font-size: 14px;
        }

        .total-section {
            background-color: white;
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--box-shadow);
            margin-top: 30px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px dashed var(--gray-light);
            font-size: 1.1rem;
        }

        .total-row:last-child {
            border-bottom: none;
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--primary);
            margin-top: 10px;
            padding-top: 20px;
        }

        .payment-section {
            margin-top: 30px;
            padding-top: 25px;
            border-top: 2px solid var(--gray-light);
        }

        .empty-cart {
            text-align: center;
            padding: 60px 20px;
            color: var(--gray);
        }

        .empty-cart i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: var(--gray-light);
        }

        .empty-cart h3 {
            font-size: 1.8rem;
            margin-bottom: 15px;
            color: var(--gray);
        }

        .scan-section {
            background-color: white;
            border-radius: var(--border-radius);
            padding: 20px;
            margin-bottom: 25px;
            box-shadow: var(--box-shadow);
        }

        .scan-input {
            font-size: 1.2rem;
            padding: 16px;
            letter-spacing: 2px;
        }

        .timer {
            display: inline-block;
            background-color: rgba(242, 153, 74, 0.1);
            color: var(--warning);
            padding: 8px 16px;
            border-radius: 50px;
            font-weight: bold;
            margin-left: 15px;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .member-info-card {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .card {
                padding: 20px 15px;
            }
            
            th, td {
                padding: 12px 8px;
            }
            
            .btn {
                padding: 10px 18px;
                font-size: 0.95rem;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <!-- Header -->
    <div class="header">
        <h1><i class="fas fa-shopping-cart"></i> Keranjang Belanja</h1>
        <div>
            <a href="../Scanner/scan.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Kembali ke Scan
            </a>
            <?php if (!empty($_SESSION['keranjang'])): ?>
                <a href="../service/hapus-semua.php" class="btn btn-danger" onclick="return confirm('Yakin hapus semua keranjang?')">
                    <i class="fas fa-trash-alt"></i> Kosongkan Keranjang
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if ($error_msg): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <div><?= htmlspecialchars($error_msg) ?></div>
        </div>
    <?php endif; ?>
    
    <?php if ($success_msg): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <div><?= htmlspecialchars($success_msg) ?></div>
        </div>
    <?php endif; ?>

    <!-- Member Search Form -->
    <div class="card">
        <h2 class="card-title"><i class="fas fa-user-circle"></i> Cari Member</h2>
        <form method="post" class="member-form">
            <div class="form-group">
                <div style="display: flex; gap: 10px; align-items: center;">
                    <input type="text" name="no_telp" class="form-control" 
                           placeholder="Masukkan Nomor Telepon Member" 
                           required value="<?= htmlspecialchars($member['no_telp'] ?? '') ?>"
                           style="flex: 1;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Cari
                    </button>
                    <?php if ($fid_member): ?>
                        <button type="submit" name="reset_member" class="btn btn-warning">
                            <i class="fas fa-times"></i> Reset Member
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </form>
        
        <?php if ($member): ?>
            <div class="member-info-card">
                <div class="member-details">
                    <h3><i class="fas fa-user-tag"></i> <?= htmlspecialchars($member['nama_member']) ?></h3>
                    <p><?= htmlspecialchars($member['no_telp']) ?> | Status: <span style="color: #4caf50; font-weight: bold;"><?= ucfirst($member['status']) ?></span></p>
                </div>
                <div class="poin-badge">
                    <i class="fas fa-coins"></i> <?= $member['poin'] ?> Poin
                    <small>(Rp <?= number_format($member['poin'] * 1000) ?>)</small>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Scan Barcode Section -->
    <div class="scan-section">
        <h3 style="margin-bottom: 15px; color: var(--primary); display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-barcode"></i> Scan Produk
            <?php if (isset($_SESSION['keranjang_waktu'])): ?>
                <span class="timer" title="Waktu checkout tersisa">
                    <i class="fas fa-clock"></i> 
                    <?php 
                    $waktu_terlewat = time() - $_SESSION['keranjang_waktu'];
                    $sisa_waktu = $waktu_expired - $waktu_terlewat;
                    $menit = floor($sisa_waktu / 60);
                    $detik = $sisa_waktu % 60;
                    echo sprintf("%02d:%02d", $menit, $detik);
                    ?>
                </span>
            <?php endif; ?>
        </h3>
        <form method="post" action="" id="scan-form" autocomplete="off">
            <input type="text" id="barcode" name="barcode" class="form-control scan-input" 
                   placeholder="Scan atau ketik barcode produk" required autofocus />
        </form>
    </div>

    <!-- Cart Items -->
    <?php if (!empty($_SESSION['keranjang'])): ?>
    <div class="card">
        <h2 class="card-title"><i class="fas fa-list-alt"></i> Daftar Produk</h2>
        
        <form method="post" action="../user/checkout.php" onsubmit="return confirm('Yakin melakukan checkout?')">
            <input type="hidden" name="total_semua" id="input-total" />
            <input type="hidden" name="total_diskon" id="input-diskon" />
            <input type="hidden" name="total_bayar" id="input-bayar" />
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 50px;">Pilih</th>
                            <th>Produk</th>
                            <th style="width: 150px;">Harga</th>
                            <th style="width: 150px;">Qty</th>
                            <th style="width: 150px;">Subtotal</th>
                            <th style="width: 100px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php 
                    $total_semua = 0;
                    foreach ($_SESSION['keranjang'] as $item): 
                        $subtotal = $item['harga'] * $item['qty'];
                        $total_semua += $subtotal;
                    ?>
                        <tr>
                            <td>
                                <label class="checkbox-container">
                                    <input type="checkbox" name="produk_dipilih[]" 
                                           value="<?= $item['id_produk'] ?>" 
                                           data-subtotal="<?= $subtotal ?>" 
                                           onchange="hitungTotal()" checked />
                                    <span class="checkbox-custom"></span>
                                </label>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($item['nama']) ?></strong><br>
                                <small style="color: var(--gray);">ID: <?= $item['id_produk'] ?></small>
                            </td>
                            <td>Rp <?= number_format($item['harga'], 0, ',', '.') ?></td>
                            <td>
                                <div class="qty-control">
                                    <a href="../service/update-qty.php?id=<?= $item['id_produk'] ?>&action=kurang" class="qty-btn">
                                        <i class="fas fa-minus"></i>
                                    </a>
                                    <span class="qty-value"><?= $item['qty'] ?></span>
                                    <a href="../service/update-qty.php?id=<?= $item['id_produk'] ?>&action=lebih" class="qty-btn">
                                        <i class="fas fa-plus"></i>
                                    </a>
                                </div>
                            </td>
                            <td>
                                <strong>Rp <?= number_format($subtotal, 0, ',', '.') ?></strong>
                            </td>
                            <td>
                                <a href="../service/hapus-keranjang.php?id=<?= $item['id_produk'] ?>" 
                                   class="action-btn btn-delete"
                                   onclick="return confirm('Hapus produk ini dari keranjang?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Total Section -->
            <div class="total-section" id="total-section">
                <h3 style="margin-bottom: 20px; color: var(--primary);">
                    <i class="fas fa-calculator"></i> Ringkasan Pembayaran
                </h3>
                
                <div class="total-row">
                    <span>Total Harga:</span>
                    <span id="total-harga">Rp 0</span>
                </div>
                
                <?php if ($member): ?>
                <div class="total-row" style="color: #2ecc71;">
                    <span>Diskon Member (<?= $member['poin'] ?> poin):</span>
                    <span id="diskon-poin">Rp 0</span>
                </div>
                <?php endif; ?>
                
                <div class="total-row">
                    <span>Total Bayar:</span>
                    <strong id="total-bayar">Rp 0</strong>
                </div>
            </div>
            
            <!-- Payment Section -->
            <div class="payment-section">
                <h3 style="margin-bottom: 20px; color: var(--primary);">
                    <i class="fas fa-credit-card"></i> Pembayaran
                </h3>
                
                <div class="form-group">
                    <label for="uang_dibayar">Uang Dibayar (Rp):</label>
                    <input type="number" name="uang_dibayar" id="uang_dibayar" 
                           class="form-control" min="0" required 
                           placeholder="Masukkan jumlah uang yang dibayar">
                </div>
                
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 30px;">
                    <div>
                        <a href="../Scanner/scan.php" class="btn btn-outline">
                            <i class="fas fa-plus-circle"></i> Tambah Produk Lain
                        </a>
                    </div>
                    
                    <button type="submit" class="btn btn-success" style="padding: 15px 40px; font-size: 1.1rem;">
                        <i class="fas fa-check-circle"></i> Proses Checkout
                    </button>
                </div>
            </div>
        </form>
    </div>
    
    <?php else: ?>
        <!-- Empty Cart State -->
        <div class="card">
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <h3>Keranjang Belanja Kosong</h3>
                <p style="margin-bottom: 25px; font-size: 1.1rem;">Mulai scan produk untuk menambahkan ke keranjang</p>
                <a href="../Scanner/scan.php" class="btn btn-primary">
                    <i class="fas fa-barcode"></i> Mulai Scan Produk
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    const poinMember = <?= $poin_member ?>;
    
    function hitungTotal() {
        let checkboxes = document.querySelectorAll('input[name="produk_dipilih[]"]:checked');
        let total = 0;
        
        checkboxes.forEach(cb => {
            total += parseInt(cb.dataset.subtotal || 0);
        });
        
        let maksimalDiskon = poinMember * 1000;
        if (maksimalDiskon > total) maksimalDiskon = total;
        const totalBayar = total - maksimalDiskon;
        
        // Update display
        document.getElementById('total-harga').textContent = 'Rp ' + formatNumber(total);
        document.getElementById('diskon-poin').textContent = 'Rp ' + formatNumber(maksimalDiskon);
        document.getElementById('total-bayar').textContent = 'Rp ' + formatNumber(totalBayar);
        
        // Update hidden inputs
        document.getElementById('input-total').value = total;
        document.getElementById('input-diskon').value = maksimalDiskon;
        document.getElementById('input-bayar').value = totalBayar;
        
        // Show/hide total section
        const totalSection = document.getElementById('total-section');
        if (totalSection) {
            totalSection.style.display = checkboxes.length ? 'block' : 'none';
        }
        
        // Auto-set uang dibayar to total bayar
        const uangDibayarInput = document.getElementById('uang_dibayar');
        if (uangDibayarInput && !uangDibayarInput.value && totalBayar > 0) {
            uangDibayarInput.value = totalBayar;
        }
    }
    
    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }
    
    // Initialize calculation on page load
    document.addEventListener('DOMContentLoaded', function() {
        hitungTotal();
        
        // Auto-submit barcode scanner
        const barcodeInput = document.getElementById('barcode');
        if (barcodeInput) {
            barcodeInput.addEventListener('change', function() {
                if (this.value.trim() !== '') {
                    document.getElementById('scan-form').submit();
                }
            });
            
            // Keep focus on barcode input
            barcodeInput.focus();
            
            // Clear input after submission
            document.getElementById('scan-form').addEventListener('submit', function() {
                setTimeout(() => {
                    barcodeInput.value = '';
                    barcodeInput.focus();
                }, 100);
            });
        }
        
        // Timer countdown for cart expiry
        <?php if (isset($_SESSION['keranjang_waktu'])): ?>
        function updateTimer() {
            const timerElement = document.querySelector('.timer');
            if (!timerElement) return;
            
            const timerText = timerElement.textContent.trim();
            const parts = timerText.split(':');
            let minutes = parseInt(parts[0]);
            let seconds = parseInt(parts[1]);
            
            if (seconds === 0) {
                if (minutes === 0) {
                    // Time's up - reload page
                    location.reload();
                    return;
                }
                minutes--;
                seconds = 59;
            } else {
                seconds--;
            }
            
            timerElement.textContent = ` ${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        }
        
        // Update timer every second
        setInterval(updateTimer, 1000);
        <?php endif; ?>
    });
</script>
</body>
</html>