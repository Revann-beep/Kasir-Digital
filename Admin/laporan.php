<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../service/index.php");
    exit;
}

include '../service/conection.php';

$start = $_GET['start_date'] ?? date('Y-m-01', strtotime('-2 month'));
$end   = $_GET['end_date'] ?? date('Y-m-d');

// Query per bulan
$query = mysqli_query($conn, "
    SELECT 
        DATE_FORMAT(t.tgl_pembelian, '%Y-%m') AS bulan,
        SUM(p.modal * dt.qty) AS total_modal,
        SUM(p.harga_jual * dt.qty) AS total_penjualan,
        SUM((p.harga_jual - p.modal) * dt.qty) AS total_untung,
        COUNT(DISTINCT t.id_transaksi) AS total_transaksi,
        SUM(dt.qty) AS total_qty
    FROM transaksi t
    JOIN detail_transaksi dt ON t.id_transaksi = dt.fid_transaksi
    JOIN produk p ON dt.fid_produk = p.id_produk
    WHERE t.tgl_pembelian BETWEEN '$start' AND '$end'
    GROUP BY DATE_FORMAT(t.tgl_pembelian, '%Y-%m')
    ORDER BY bulan DESC
");

// Persiapkan data chart
$labels = [];
$dataKeuntungan = [];
$dataQty = [];
$dataPenjualan = [];
$dataTransaksi = [];

$data_rows = [];
$total_keuntungan_all = 0;
$total_penjualan_all = 0;
$total_transaksi_all = 0;

while ($row = mysqli_fetch_assoc($query)) {
    $data_rows[] = $row;

    $bulan = date('F Y', strtotime($row['bulan'] . '-01'));
    $labels[] = $bulan;
    $dataKeuntungan[] = $row['total_untung'];
    $dataQty[] = $row['total_qty'];
    $dataPenjualan[] = $row['total_penjualan'];
    $dataTransaksi[] = $row['total_transaksi'];
    
    $total_keuntungan_all += $row['total_untung'];
    $total_penjualan_all += $row['total_penjualan'];
    $total_transaksi_all += $row['total_transaksi'];
}

// Query untuk top produk
$topProdukQuery = mysqli_query($conn, "
    SELECT 
        p.nama_produk,
        SUM(dt.qty) as total_terjual,
        SUM((p.harga_jual - p.modal) * dt.qty) as keuntungan
    FROM detail_transaksi dt
    JOIN produk p ON dt.fid_produk = p.id_produk
    JOIN transaksi t ON dt.fid_transaksi = t.id_transaksi
    WHERE t.tgl_pembelian BETWEEN '$start' AND '$end'
    GROUP BY p.id_produk
    ORDER BY total_terjual DESC
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Bulanan - TimelessWatch.co</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #1a237e;
            --primary-light: #534bae;
            --primary-dark: #000051;
            --secondary: #ff9800;
            --accent: #00bcd4;
            --success: #4caf50;
            --warning: #ff9800;
            --danger: #f44336;
            --info: #2196f3;
            --light: #f5f5f5;
            --dark: #212121;
            --gray: #757575;
            --gray-light: #e0e0e0;
            --border-radius: 12px;
            --box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', 'Roboto', sans-serif;
        }

        body {
            display: flex;
            min-height: 100vh;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
            color: var(--dark);
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, var(--primary) 0%, var(--primary-dark) 100%);
            padding: 30px 20px;
            color: white;
            display: flex;
            flex-direction: column;
            box-shadow: 4px 0 15px rgba(0, 0, 0, 0.1);
            position: fixed;
            height: 100vh;
            z-index: 100;
        }

        .logo {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid rgba(255, 255, 255, 0.1);
        }

        .logo h2 {
            font-size: 24px;
            font-weight: 700;
            letter-spacing: 1.5px;
            color: white;
            margin-bottom: 5px;
        }

        .logo p {
            font-size: 12px;
            opacity: 0.8;
        }

        .sidebar-nav ul {
            list-style: none;
            flex-grow: 1;
        }

        .sidebar-nav ul li {
            margin-bottom: 10px;
        }

        .sidebar-nav ul li a {
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 20px;
            border-radius: var(--border-radius);
            transition: var(--transition);
        }

        .sidebar-nav ul li a:hover {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            transform: translateX(5px);
        }

        .sidebar-nav ul li a.active {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .sidebar-nav ul li a i {
            width: 24px;
            text-align: center;
            font-size: 18px;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 30px;
            display: flex;
            flex-direction: column;
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            padding: 25px 30px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin-bottom: 30px;
        }

        .page-title h1 {
            font-size: 28px;
            color: var(--primary);
            font-weight: 700;
            margin-bottom: 8px;
        }

        .page-title p {
            color: var(--gray);
            font-size: 14px;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            border-left: 5px solid var(--primary);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .stat-card.orange { border-left-color: var(--secondary); }
        .stat-card.green { border-left-color: var(--success); }
        .stat-card.blue { border-left-color: var(--info); }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            font-size: 24px;
            color: white;
        }

        .stat-card:nth-child(1) .stat-icon { background: var(--primary); }
        .stat-card:nth-child(2) .stat-icon { background: var(--secondary); }
        .stat-card:nth-child(3) .stat-icon { background: var(--success); }
        .stat-card:nth-child(4) .stat-icon { background: var(--info); }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 14px;
            color: var(--gray);
            font-weight: 500;
        }

        /* Filter Section */
        .filter-section {
            background: white;
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--box-shadow);
            margin-bottom: 30px;
        }

        .filter-section h3 {
            font-size: 18px;
            color: var(--primary);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .filter-form {
            display: flex;
            gap: 15px;
            align-items: flex-end;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .filter-group label {
            font-weight: 600;
            color: var(--dark);
            font-size: 14px;
        }

        .filter-input {
            padding: 12px 15px;
            border: 2px solid var(--gray-light);
            border-radius: var(--border-radius);
            font-size: 14px;
            transition: var(--transition);
            min-width: 200px;
        }

        .filter-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(26, 35, 126, 0.1);
        }

        /* Buttons */
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: var(--border-radius);
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(26, 35, 126, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(26, 35, 126, 0.4);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success) 0%, #2e7d32 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3);
        }

        .btn-success:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(76, 175, 80, 0.4);
        }

        .btn-warning {
            background: linear-gradient(135deg, var(--warning) 0%, #ef6c00 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(255, 152, 0, 0.3);
        }

        .btn-warning:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(255, 152, 0, 0.4);
        }

        /* Report Table */
        .table-section {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--border-radius);
            overflow: hidden;
            margin-bottom: 30px;
        }

        .table-header {
            padding: 25px 30px;
            border-bottom: 1px solid var(--gray-light);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-header h2 {
            font-size: 20px;
            color: var(--primary);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: linear-gradient(90deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
        }

        th {
            padding: 18px 15px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: none;
        }

        th:first-child {
            padding-left: 30px;
        }

        th:last-child {
            padding-right: 30px;
        }

        tbody tr {
            border-bottom: 1px solid var(--gray-light);
            transition: var(--transition);
        }

        tbody tr:hover {
            background-color: rgba(26, 35, 126, 0.05);
        }

        td {
            padding: 20px 15px;
            font-size: 14px;
            color: var(--dark);
        }

        td:first-child {
            padding-left: 30px;
        }

        td:last-child {
            padding-right: 30px;
        }

        /* Profit/Loss Color */
        .profit {
            color: var(--success);
            font-weight: 600;
        }

        /* Charts Section */
        .charts-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
        }

        .chart-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--box-shadow);
        }

        .chart-card h3 {
            font-size: 18px;
            color: var(--primary);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .chart-container {
            height: 300px;
            position: relative;
        }

        /* Top Products */
        .top-products {
            background: white;
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--box-shadow);
            margin-bottom: 30px;
        }

        .top-products h3 {
            font-size: 18px;
            color: var(--primary);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .product-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px;
            border-bottom: 1px solid var(--gray-light);
            transition: var(--transition);
        }

        .product-item:hover {
            background: rgba(26, 35, 126, 0.05);
        }

        .product-item:last-child {
            border-bottom: none;
        }

        .product-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .product-rank {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
        }

        .product-name {
            font-weight: 600;
            color: var(--dark);
        }

        .product-stats {
            text-align: right;
        }

        .product-sold {
            font-weight: 600;
            color: var(--primary);
        }

        .product-profit {
            font-size: 12px;
            color: var(--success);
        }

        /* Footer */
        .footer {
            margin-top: auto;
            text-align: center;
            padding: 20px;
            color: var(--gray);
            font-size: 14px;
            border-top: 1px solid var(--gray-light);
        }

        /* Print Styles */
        @media print {
            .sidebar, .filter-section, .stats-grid, .btn, .charts-section, .top-products, .footer {
                display: none !important;
            }
            .main-content {
                margin-left: 0;
                padding: 0;
            }
            .header, .table-section {
                box-shadow: none;
                border: 1px solid #ddd;
            }
            table {
                break-inside: avoid;
            }
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .sidebar {
                width: 250px;
            }
            .main-content {
                margin-left: 250px;
                padding: 20px;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                padding: 20px;
            }
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
            .header {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
            .filter-form {
                flex-direction: column;
                align-items: stretch;
            }
            .filter-input {
                min-width: 100%;
            }
            .charts-section {
                grid-template-columns: 1fr;
            }
            .table-section {
                overflow-x: auto;
            }
            table {
                min-width: 800px;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            .chart-card {
                padding: 15px;
            }
            .chart-container {
                height: 250px;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <h2>TimelessWatch.co</h2>
            <p>Analytics & Reports</p>
        </div>
        
        <nav class="sidebar-nav">
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="kategori.php"><i class="fas fa-tags"></i> Kategori</a></li>
                <li><a href="produk.php"><i class="fas fa-box-open"></i> Produk</a></li>
                <li><a href="member.php"><i class="fas fa-users"></i> Member</a></li>
                <li><a href="admin.php"><i class="fas fa-user-shield"></i> Admin</a></li>
                <li><a href="laporan.php" class="active"><i class="fas fa-chart-bar"></i> Laporan</a></li>
                <li><a href="transaksi.php"><i class="fas fa-shopping-cart"></i> Transaksi</a></li>
                <li><a href="../service/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="header">
            <div class="page-title">
                <h1><i class="fas fa-chart-line"></i> Laporan & Analytics</h1>
                <p>Analisis kinerja bisnis periode <?= date('d/m/Y', strtotime($start)) ?> - <?= date('d/m/Y', strtotime($end)) ?></p>
            </div>
            
            <div class="header-actions">
                <button class="btn btn-warning" onclick="window.print()">
                    <i class="fas fa-print"></i> Cetak Laporan
                </button>
                <button class="btn btn-success" onclick="exportToExcel()">
                    <i class="fas fa-file-excel"></i> Export Excel
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="stat-value">Rp <?= number_format($total_penjualan_all, 0, ',', '.') ?></div>
                <div class="stat-label">Total Penjualan</div>
            </div>
            
            <div class="stat-card orange">
                <div class="stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-value">Rp <?= number_format($total_keuntungan_all, 0, ',', '.') ?></div>
                <div class="stat-label">Total Keuntungan</div>
            </div>
            
            <div class="stat-card green">
                <div class="stat-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-value"><?= number_format($total_transaksi_all, 0, ',', '.') ?></div>
                <div class="stat-label">Total Transaksi</div>
            </div>
            
            <div class="stat-card blue">
                <div class="stat-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="stat-value"><?= count($data_rows) ?></div>
                <div class="stat-label">Bulan Dilaporkan</div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <h3><i class="fas fa-filter"></i> Filter Periode Laporan</h3>
            
            <form method="get" class="filter-form">
                <div class="filter-group">
                    <label for="start_date">Dari Tanggal</label>
                    <input type="date" 
                           id="start_date" 
                           name="start_date" 
                           class="filter-input" 
                           value="<?= $start ?>" 
                           required>
                </div>
                
                <div class="filter-group">
                    <label for="end_date">Sampai Tanggal</label>
                    <input type="date" 
                           id="end_date" 
                           name="end_date" 
                           class="filter-input" 
                           value="<?= $end ?>" 
                           required>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Tampilkan Laporan
                </button>
                
                <button type="button" class="btn" 
                        onclick="window.location.href='laporan.php'"
                        style="background: var(--gray); color: white;">
                    <i class="fas fa-times"></i> Reset
                </button>
            </form>
        </div>

        <!-- Charts Section -->
        <div class="charts-section">
            <div class="chart-card">
                <h3><i class="fas fa-chart-bar"></i> Keuntungan Bulanan</h3>
                <div class="chart-container">
                    <canvas id="chartKeuntungan"></canvas>
                </div>
            </div>
            
            <div class="chart-card">
                <h3><i class="fas fa-chart-pie"></i> Distribusi Transaksi</h3>
                <div class="chart-container">
                    <canvas id="chartTransaksi"></canvas>
                </div>
            </div>
        </div>

        <!-- Top Products -->
        <?php if (mysqli_num_rows($topProdukQuery) > 0): ?>
        <div class="top-products">
            <h3><i class="fas fa-trophy"></i> Top 5 Produk Terlaris</h3>
            
            <?php 
            $rank = 1;
            mysqli_data_seek($topProdukQuery, 0);
            while ($produk = mysqli_fetch_assoc($topProdukQuery)): 
            ?>
            <div class="product-item">
                <div class="product-info">
                    <span class="product-rank"><?= $rank ?></span>
                    <div>
                        <div class="product-name"><?= htmlspecialchars($produk['nama_produk']) ?></div>
                        <div style="font-size: 12px; color: var(--gray);">
                            Keuntungan: Rp <?= number_format($produk['keuntungan'], 0, ',', '.') ?>
                        </div>
                    </div>
                </div>
                <div class="product-stats">
                    <div class="product-sold"><?= number_format($produk['total_terjual']) ?> terjual</div>
                </div>
            </div>
            <?php 
                $rank++;
            endwhile; 
            ?>
        </div>
        <?php endif; ?>

        <!-- Report Table -->
        <div class="table-section">
            <div class="table-header">
                <h2><i class="fas fa-table"></i> Laporan Bulanan Detail</h2>
                <span style="color: var(--gray); font-size: 14px;">
                    Periode: <?= date('d/m/Y', strtotime($start)) ?> - <?= date('d/m/Y', strtotime($end)) ?>
                </span>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Bulan</th>
                        <th>Total Penjualan</th>
                        <th>Total Modal</th>
                        <th>Total Keuntungan</th>
                        <th>Total Transaksi</th>
                        <th>Produk Terjual</th>
                        <th>Rata-rata/Transaksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    foreach ($data_rows as $row):
                        $bulanFormatted = date('F Y', strtotime($row['bulan'] . '-01'));
                        $avg_per_transaction = $row['total_transaksi'] > 0 ? $row['total_penjualan'] / $row['total_transaksi'] : 0;
                    ?>
                    <tr>
                        <td><?= $no ?></td>
                        <td><strong><?= $bulanFormatted ?></strong></td>
                        <td>Rp <?= number_format($row['total_penjualan'], 0, ',', '.') ?></td>
                        <td>Rp <?= number_format($row['total_modal'], 0, ',', '.') ?></td>
                        <td class="profit">Rp <?= number_format($row['total_untung'], 0, ',', '.') ?></td>
                        <td><?= number_format($row['total_transaksi']) ?></td>
                        <td><?= number_format($row['total_qty']) ?></td>
                        <td>Rp <?= number_format($avg_per_transaction, 0, ',', '.') ?></td>
                    </tr>
                    <?php 
                        $no++;
                    endforeach; 
                    
                    if (empty($data_rows)):
                    ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 40px; color: var(--gray);">
                            <i class="fas fa-chart-bar" style="font-size: 48px; margin-bottom: 15px; opacity: 0.3;"></i>
                            <p>Tidak ada data transaksi pada periode yang dipilih</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>&copy; <?= date('Y') ?> TimelessWatch.co - Analytics & Reporting System</p>
            <p style="font-size: 12px; margin-top: 5px;">
                <i class="fas fa-info-circle"></i> Laporan dihasilkan pada <?= date('d/m/Y H:i:s') ?>
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script>
        // Chart Keuntungan
        const ctxKeuntungan = document.getElementById('chartKeuntungan').getContext('2d');
        new Chart(ctxKeuntungan, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_reverse($labels)) ?>,
                datasets: [{
                    label: 'Keuntungan (Rp)',
                    data: <?= json_encode(array_reverse($dataKeuntungan)) ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.8)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Rp ' + context.raw.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    }
                }
            }
        });

        // Chart Transaksi
        const ctxTransaksi = document.getElementById('chartTransaksi').getContext('2d');
        new Chart(ctxTransaksi, {
            type: 'pie',
            data: {
                labels: <?= json_encode(array_reverse($labels)) ?>,
                datasets: [{
                    data: <?= json_encode(array_reverse($dataTransaksi)) ?>,
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', 
                        '#9966FF', '#FF9F40', '#8AC926', '#1982C4'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.raw;
                                const total = <?= array_sum($dataTransaksi) ?>;
                                const percentage = Math.round((value / total) * 100);
                                return `${context.label}: ${value} transaksi (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });

        // Export to Excel
        function exportToExcel() {
            const table = document.querySelector('table');
            const ws = XLSX.utils.table_to_sheet(table);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, "Laporan Bulanan");
            
            const filename = `Laporan_<?= date('Y-m-d') ?>_<?= date('His') ?>.xlsx`;
            XLSX.writeFile(wb, filename);
        }

        // Auto update chart on window resize
        window.addEventListener('resize', function() {
            chartKeuntungan.resize();
            chartTransaksi.resize();
        });

        // Add print confirmation
        window.addEventListener('beforeprint', () => {
            alert('Mempersiapkan dokumen untuk dicetak...');
        });

        // Add keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl + P untuk print
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                window.print();
            }
            
            // Ctrl + E untuk export Excel
            if (e.ctrlKey && e.key === 'e') {
                e.preventDefault();
                exportToExcel();
            }
        });

        // Show help tooltip
        setTimeout(() => {
            if (!localStorage.getItem('laporan_help_shown')) {
                alert('Tips:\n- Gunakan Ctrl+P untuk print cepat\n- Gunakan Ctrl+E untuk export Excel\n- Klik pada filter untuk ubah periode');
                localStorage.setItem('laporan_help_shown', 'true');
            }
        }, 1000);
    </script>
</body>
</html>
<?php mysqli_close($conn); ?>