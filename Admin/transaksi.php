<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../service/index.php");
    exit;
}

include '../service/conection.php';

// Pagination setup
$limit = 10; // transaksi per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Filter setup
$filter_date = $_GET['filter_date'] ?? '';
$filter_member = $_GET['filter_member'] ?? '';
$filter_admin = $_GET['filter_admin'] ?? '';

// Build WHERE clause for filters
$where_clause = "WHERE 1=1";
if (!empty($filter_date)) {
    $where_clause .= " AND DATE(t.tgl_pembelian) = '$filter_date'";
}
if (!empty($filter_member)) {
    $where_clause .= " AND m.nama_member LIKE '%$filter_member%'";
}
if (!empty($filter_admin)) {
    $where_clause .= " AND a.username LIKE '%$filter_admin%'";
}

// Hitung statistik
$today_sales = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COALESCE(SUM(total_bayar), 0) as total 
    FROM transaksi 
    WHERE DATE(tgl_pembelian) = CURDATE()
"))['total'] ?? 0;

$total_transactions = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) as total FROM transaksi
"))['total'] ?? 0;

$total_revenue = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COALESCE(SUM(total_bayar), 0) as total FROM transaksi
"))['total'] ?? 0;

// Ambil total data transaksi untuk paging dengan filter
$total_query = mysqli_query($conn, "
    SELECT COUNT(*) as total 
    FROM transaksi t
    LEFT JOIN member m ON t.fid_member = m.id_member
    LEFT JOIN admin a ON t.fid_admin = a.id
    $where_clause
");
$total_data = mysqli_fetch_assoc($total_query)['total'];
$total_pages = ceil($total_data / $limit);

// Query data transaksi dengan limit dan offset
$query = "
    SELECT 
        t.*,
        m.nama_member,
        m.no_telp,
        a.username as admin_name,
        COUNT(dt.id_detail) as total_items,
        SUM(dt.qty) as total_qty
    FROM transaksi t
    LEFT JOIN member m ON t.fid_member = m.id_member
    LEFT JOIN admin a ON t.fid_admin = a.id
    LEFT JOIN detail_transaksi dt ON t.id_transaksi = dt.fid_transaksi
    $where_clause
    GROUP BY t.id_transaksi
    ORDER BY t.id_transaksi DESC
    LIMIT $limit OFFSET $offset
";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Riwayat Transaksi - TimelessWatch.co</title>
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

        .header-actions {
            display: flex;
            gap: 15px;
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
        .stat-card.cyan { border-left-color: var(--accent); }

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

        .btn-danger {
            background: linear-gradient(135deg, var(--danger) 0%, #c62828 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(244, 67, 54, 0.3);
        }

        .btn-danger:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(244, 67, 54, 0.4);
        }

        /* Transactions Table */
        .table-section {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
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

        /* Transaction ID */
        .transaction-id {
            font-weight: 600;
            color: var(--primary);
            font-family: 'Courier New', monospace;
        }

        /* Member Info */
        .member-info {
            display: flex;
            flex-direction: column;
        }

        .member-name {
            font-weight: 600;
            color: var(--dark);
        }

        .member-phone {
            font-size: 12px;
            color: var(--gray);
        }

        /* Payment Info */
        .payment-info {
            display: flex;
            flex-direction: column;
        }

        .total-amount {
            font-weight: 700;
            color: var(--success);
            font-family: 'Courier New', monospace;
        }

        .payment-details {
            font-size: 12px;
            color: var(--gray);
        }

        /* Status Badge */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-completed {
            background: rgba(76, 175, 80, 0.1);
            color: var(--success);
            border: 1px solid rgba(76, 175, 80, 0.3);
        }

        /* Items Badge */
        .items-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            background: rgba(33, 150, 243, 0.1);
            color: var(--info);
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            border: 1px solid rgba(33, 150, 243, 0.3);
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .btn-action {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
            color: white;
            font-size: 16px;
        }

        .btn-view {
            background: linear-gradient(135deg, var(--accent) 0%, #00838f 100%);
        }

        .btn-view:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 188, 212, 0.3);
        }

        .btn-print {
            background: linear-gradient(135deg, var(--secondary) 0%, #ef6c00 100%);
        }

        .btn-print:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(255, 152, 0, 0.3);
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 30px;
        }

        .page-link {
            padding: 10px 16px;
            border-radius: var(--border-radius);
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: var(--transition);
            background: white;
            color: var(--dark);
            border: 2px solid var(--gray-light);
            min-width: 45px;
            text-align: center;
        }

        .page-link:hover:not(.active) {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .page-link.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
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

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--gray);
        }

        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
            color: var(--gray-light);
            opacity: 0.5;
        }

        .empty-state h3 {
            font-size: 20px;
            color: var(--gray);
            margin-bottom: 10px;
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
            .header-actions {
                flex-direction: column;
                width: 100%;
            }
            .filter-form {
                flex-direction: column;
                align-items: stretch;
            }
            .filter-input {
                min-width: 100%;
            }
            .table-section {
                overflow-x: auto;
            }
            table {
                min-width: 1000px;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            .action-buttons {
                flex-direction: column;
                gap: 5px;
            }
            .page-link {
                padding: 8px 12px;
                min-width: 40px;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <h2>TimelessWatch.co</h2>
            <p>Transaction History</p>
        </div>
        
        <nav class="sidebar-nav">
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="kategori.php"><i class="fas fa-tags"></i> Kategori</a></li>
                <li><a href="produk.php"><i class="fas fa-box-open"></i> Produk</a></li>
                <li><a href="member.php"><i class="fas fa-users"></i> Member</a></li>
                <li><a href="admin.php"><i class="fas fa-user-shield"></i> Admin</a></li>
                <li><a href="laporan.php"><i class="fas fa-chart-bar"></i> Laporan</a></li>
                <li><a href="transaksi.php" class="active"><i class="fas fa-shopping-cart"></i> Transaksi</a></li>
                <li><a href="../user/keranjang.php"><i class="fas fa-cart-shopping"></i> Kasir</a></li>
                <li><a href="../service/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="header">
            <div class="page-title">
                <h1><i class="fas fa-shopping-cart"></i> Riwayat Transaksi</h1>
                <p>Monitor dan kelola seluruh transaksi yang terjadi</p>
            </div>
            
            <div class="header-actions">
                <a href="../user/keranjang.php" class="btn btn-success">
                    <i class="fas fa-cash-register"></i> Buat Transaksi Baru
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="stat-value">Rp <?= number_format($today_sales, 0, ',', '.') ?></div>
                <div class="stat-label">Penjualan Hari Ini</div>
            </div>
            
            <div class="stat-card orange">
                <div class="stat-icon">
                    <i class="fas fa-receipt"></i>
                </div>
                <div class="stat-value"><?= number_format($total_transactions, 0, ',', '.') ?></div>
                <div class="stat-label">Total Transaksi</div>
            </div>
            
            <div class="stat-card green">
                <div class="stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-value">Rp <?= number_format($total_revenue, 0, ',', '.') ?></div>
                <div class="stat-label">Total Pendapatan</div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <h3><i class="fas fa-filter"></i> Filter Transaksi</h3>
            
            <form method="GET" class="filter-form">
                <div class="filter-group">
                    <label for="filter_date">Tanggal Transaksi</label>
                    <input type="date" 
                           id="filter_date" 
                           name="filter_date" 
                           class="filter-input" 
                           value="<?= htmlspecialchars($filter_date) ?>">
                </div>
                
                <div class="filter-group">
                    <label for="filter_member">Nama Member</label>
                    <input type="text" 
                           id="filter_member" 
                           name="filter_member" 
                           class="filter-input" 
                           placeholder="Cari berdasarkan nama member..."
                           value="<?= htmlspecialchars($filter_member) ?>">
                </div>
                
                <div class="filter-group">
                    <label for="filter_admin">Nama Admin</label>
                    <input type="text" 
                           id="filter_admin" 
                           name="filter_admin" 
                           class="filter-input" 
                           placeholder="Cari berdasarkan nama admin..."
                           value="<?= htmlspecialchars($filter_admin) ?>">
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Terapkan Filter
                </button>
                
                <?php if ($filter_date || $filter_member || $filter_admin): ?>
                <a href="transaksi.php" class="btn" style="background: var(--gray); color: white;">
                    <i class="fas fa-times"></i> Reset Filter
                </a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Transactions Table -->
        <div class="table-section">
            <div class="table-header">
                <h2><i class="fas fa-list"></i> Daftar Transaksi</h2>
                <span style="color: var(--gray); font-size: 14px;">
                    <?= $total_data ?> transaksi ditemukan
                </span>
            </div>

            <?php if (mysqli_num_rows($result) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID Transaksi</th>
                        <th>Tanggal & Waktu</th>
                        <th>Admin</th>
                        <th>Member</th>
                        <th>Items</th>
                        <th>Total Pembayaran</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): 
                        $tanggal = date('d/m/Y', strtotime($row['tgl_pembelian']));
                        $waktu = date('H:i', strtotime($row['tgl_pembelian']));
                    ?>
                    <tr>
                        <td>
                            <span class="transaction-id">#<?= $row['id_transaksi'] ?></span>
                        </td>
                        <td>
                            <div>
                                <strong><?= $tanggal ?></strong>
                                <br>
                                <small style="color: var(--gray);"><?= $waktu ?></small>
                            </div>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($row['admin_name']) ?></strong>
                        </td>
                        <td>
                            <?php if ($row['nama_member']): ?>
                            <div class="member-info">
                                <span class="member-name"><?= htmlspecialchars($row['nama_member']) ?></span>
                                <span class="member-phone"><?= htmlspecialchars($row['no_telp']) ?></span>
                            </div>
                            <?php else: ?>
                            <span style="color: var(--gray); font-style: italic;">Non-member</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="items-badge">
                                <i class="fas fa-box"></i> 
                                <?= $row['total_items'] ?? 0 ?> items 
                                (<?= $row['total_qty'] ?? 0 ?> pcs)
                            </span>
                        </td>
                        <td>
                            <div class="payment-info">
                                <span class="total-amount">
                                    Rp <?= number_format($row['total_bayar'], 0, ',', '.') ?>
                                </span>
                                <span class="payment-details">
                                    Dibayar: Rp <?= number_format($row['uang_dibayar'], 0, ',', '.') ?>
                                </span>
                            </div>
                        </td>
                        <td>
                            <span class="status-badge status-completed">
                                <i class="fas fa-check-circle"></i> Selesai
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-action btn-view" 
                                        onclick="viewTransaction(<?= $row['id_transaksi'] ?>)"
                                        title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </button>
                                
                                <button class="btn-action btn-print" 
                                        onclick="printReceipt(<?= $row['id_transaksi'] ?>)"
                                        title="Cetak Struk">
                                    <i class="fas fa-print"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-shopping-cart"></i>
                <h3>Tidak ada transaksi ditemukan</h3>
                <p><?= $filter_date || $filter_member || $filter_admin ? "Coba gunakan filter yang berbeda" : "Belum ada transaksi yang tercatat" ?></p>
                <a href="../user/keranjang.php" class="btn btn-primary" style="margin-top: 20px;">
                    <i class="fas fa-cash-register"></i> Buat Transaksi Pertama
                </a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page-1 ?>&filter_date=<?= $filter_date ?>&filter_member=<?= $filter_member ?>&filter_admin=<?= $filter_admin ?>" 
                   class="page-link">
                    <i class="fas fa-chevron-left"></i>
                </a>
            <?php endif; ?>
            
            <?php 
            $start = max(1, $page - 2);
            $end = min($total_pages, $page + 2);
            
            for ($i = $start; $i <= $end; $i++): ?>
                <a href="?page=<?= $i ?>&filter_date=<?= $filter_date ?>&filter_member=<?= $filter_member ?>&filter_admin=<?= $filter_admin ?>" 
                   class="page-link <?= $i == $page ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
            
            <?php if ($page < $total_pages): ?>
                <a href="?page=<?= $page+1 ?>&filter_date=<?= $filter_date ?>&filter_member=<?= $filter_member ?>&filter_admin=<?= $filter_admin ?>" 
                   class="page-link">
                    <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Footer -->
        <div class="footer">
            <p>&copy; <?= date('Y') ?> TimelessWatch.co - Transaction Management System</p>
            <p style="font-size: 12px; margin-top: 5px;">
                <i class="fas fa-info-circle"></i> Data transaksi diupdate secara real-time
            </p>
        </div>
    </div>

    <script>
        // View transaction details
        function viewTransaction(id) {
            window.open(`../user/struk.php?id_transaksi=${id}`, '_blank');
        }

        // Print receipt
        function printReceipt(id) {
            const printWindow = window.open(`../user/struk.php?id_transaksi=${id}&print=true`, '_blank');
            setTimeout(() => {
                printWindow.print();
            }, 500);
        }

        // Auto submit form on Enter in filter inputs
        document.querySelectorAll('.filter-input').forEach(input => {
            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.form.submit();
                }
            });
        });

        // Add date picker with range
        const dateInput = document.getElementById('filter_date');
        if (dateInput) {
            dateInput.addEventListener('change', function() {
                this.form.submit();
            });
        }

        // Export to Excel (optional feature)
        function exportToExcel() {
            const table = document.querySelector('table');
            const rows = table.querySelectorAll('tr');
            let csv = [];
            
            rows.forEach(row => {
                const rowData = [];
                row.querySelectorAll('th, td').forEach(cell => {
                    let text = cell.innerText.replace(/,/g, '');
                    text = text.replace(/Rp\s?/g, '');
                    rowData.push(`"${text}"`);
                });
                csv.push(rowData.join(','));
            });
            
            const csvContent = "data:text/csv;charset=utf-8," + csv.join("\n");
            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", `transaksi_<?= date('Y-m-d') ?>.csv`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // Add keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl + F untuk focus filter
            if (e.ctrlKey && e.key === 'f') {
                e.preventDefault();
                const filterInput = document.querySelector('.filter-input');
                if (filterInput) filterInput.focus();
            }
            
            // Ctrl + E untuk export
            if (e.ctrlKey && e.key === 'e') {
                e.preventDefault();
                exportToExcel();
            }
        });

        // Show loading indicator
        document.querySelectorAll('.btn-action, .btn').forEach(button => {
            button.addEventListener('click', function(e) {
                if (!this.disabled && !this.classList.contains('page-link')) {
                    const originalHTML = this.innerHTML;
                    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                    this.disabled = true;
                    
                    setTimeout(() => {
                        this.innerHTML = originalHTML;
                        this.disabled = false;
                    }, 2000);
                }
            });
        });
    </script>
</body>
</html>
<?php mysqli_close($conn); ?>