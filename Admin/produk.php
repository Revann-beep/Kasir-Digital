<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../service/index.php");
    exit;
}

include '../service/conection.php';

// Ambil semua kategori untuk dropdown
$kategoriResult = mysqli_query($conn, "SELECT * FROM kategori ORDER BY nama_kategori");

// Konfigurasi pagination
$limit = 10;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Ambil parameter pencarian dan kategori
$keyword = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$filter_kategori = isset($_GET['kategori']) ? mysqli_real_escape_string($conn, $_GET['kategori']) : '';

// Hitung total data
$countQuery = "SELECT COUNT(*) AS total FROM produk 
               JOIN kategori ON produk.fid_kategori = kategori.id_kategori
               WHERE 1";

if (!empty($keyword)) {
    $countQuery .= " AND (produk.nama_produk LIKE '%$keyword%' OR produk.barcode LIKE '%$keyword%')";
}
if (!empty($filter_kategori)) {
    $countQuery .= " AND produk.fid_kategori = '$filter_kategori'";
}

$countResult = mysqli_query($conn, $countQuery);
$totalData = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($totalData / $limit);

// Query produk
$query = "SELECT produk.*, kategori.nama_kategori 
          FROM produk 
          JOIN kategori ON produk.fid_kategori = kategori.id_kategori
          WHERE 1";

if (!empty($keyword)) {
    $query .= " AND (produk.nama_produk LIKE '%$keyword%' OR produk.barcode LIKE '%$keyword%')";
}
if (!empty($filter_kategori)) {
    $query .= " AND produk.fid_kategori = '$filter_kategori'";
}
$query .= " ORDER BY produk.id_produk DESC LIMIT $start, $limit";
$result = mysqli_query($conn, $query);

// Statistik
$totalProduk = $totalData;
$produkTerjual = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT SUM(dt.qty) as total FROM detail_transaksi dt
    JOIN transaksi t ON dt.fid_transaksi = t.id_transaksi
    WHERE DATE(t.tgl_pembelian) = CURDATE()
"))['total'] ?? 0;

$stokRendah = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) as total FROM produk WHERE stok <= 5
"))['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Manajemen Produk - TimelessWatch.co</title>
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
        .stat-card.red { border-left-color: var(--danger); }
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
        .stat-card:nth-child(3) .stat-icon { background: var(--danger); }

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
            flex: 1;
            min-width: 200px;
        }

        .filter-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark);
            font-size: 14px;
        }

        .filter-input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid var(--gray-light);
            border-radius: var(--border-radius);
            font-size: 14px;
            transition: var(--transition);
            background: white;
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
            text-align: center;
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

        /* Products Table */
        .table-container {
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
            text-align: center;
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
            vertical-align: middle;
        }

        td:first-child {
            padding-left: 30px;
        }

        td:last-child {
            padding-right: 30px;
            text-align: center;
        }

        /* Product Image */
        .product-image {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            object-fit: cover;
            border: 2px solid var(--gray-light);
            transition: var(--transition);
        }

        .product-image:hover {
            transform: scale(1.1);
            border-color: var(--primary);
        }

        /* Stok Badge */
        .stok-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .stok-high {
            background: rgba(76, 175, 80, 0.1);
            color: var(--success);
            border: 1px solid rgba(76, 175, 80, 0.3);
        }

        .stok-medium {
            background: rgba(255, 152, 0, 0.1);
            color: var(--warning);
            border: 1px solid rgba(255, 152, 0, 0.3);
        }

        .stok-low {
            background: rgba(244, 67, 54, 0.1);
            color: var(--danger);
            border: 1px solid rgba(244, 67, 54, 0.3);
        }

        /* Price Display */
        .price-display {
            font-weight: 600;
            font-family: 'Courier New', monospace;
        }

        /* Barcode */
        .barcode-container {
            text-align: center;
        }

        .barcode-img {
            width: 120px;
            height: 40px;
            cursor: pointer;
            transition: var(--transition);
            border-radius: 4px;
        }

        .barcode-img:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        /* Category Badge */
        .category-badge {
            display: inline-block;
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
            justify-content: center;
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

        .btn-edit {
            background: linear-gradient(135deg, var(--accent) 0%, #00838f 100%);
        }

        .btn-edit:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 188, 212, 0.3);
        }

        .btn-delete {
            background: linear-gradient(135deg, var(--danger) 0%, #c62828 100%);
        }

        .btn-delete:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(244, 67, 54, 0.3);
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
            .filter-group {
                min-width: 100%;
            }
            .table-container {
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
            <p>Produk Management</p>
        </div>
        
        <nav class="sidebar-nav">
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="kategori.php"><i class="fas fa-tags"></i> Kategori</a></li>
                <li><a href="produk.php" class="active"><i class="fas fa-box-open"></i> Produk</a></li>
                <li><a href="member.php"><i class="fas fa-users"></i> Member</a></li>
                <li><a href="admin.php"><i class="fas fa-user-shield"></i> Admin</a></li>
                <li><a href="laporan.php"><i class="fas fa-chart-bar"></i> Laporan</a></li>
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
                <h1><i class="fas fa-box-open"></i> Manajemen Produk</h1>
                <p>Kelola inventori produk toko Anda</p>
            </div>
            
            <div class="header-actions">
                <a href="../user/keranjang.php" class="btn btn-success">
                    <i class="fas fa-shopping-cart"></i> Keranjang
                </a>
                <a href="../service/add-produk.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah Produk
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-boxes"></i>
                </div>
                <div class="stat-value"><?= $totalProduk ?></div>
                <div class="stat-label">Total Produk</div>
            </div>
            
            <div class="stat-card orange">
                <div class="stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-value"><?= $produkTerjual ?></div>
                <div class="stat-label">Produk Terjual Hari Ini</div>
            </div>
            
            <div class="stat-card red">
                <div class="stat-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-value"><?= $stokRendah ?></div>
                <div class="stat-label">Stok Sedikit (≤ 5)</div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <h3><i class="fas fa-filter"></i> Filter Produk</h3>
            
            <form method="GET" class="filter-form">
                <div class="filter-group">
                    <label for="search">Cari Produk</label>
                    <input type="text" 
                           id="search" 
                           name="search" 
                           class="filter-input" 
                           placeholder="Nama produk atau barcode..." 
                           value="<?= htmlspecialchars($keyword) ?>">
                </div>
                
                <div class="filter-group">
                    <label for="kategori">Kategori</label>
                    <select id="kategori" name="kategori" class="filter-input">
                        <option value="">Semua Kategori</option>
                        <?php while ($kat = mysqli_fetch_assoc($kategoriResult)): ?>
                            <option value="<?= $kat['id_kategori'] ?>" 
                                <?= $filter_kategori == $kat['id_kategori'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($kat['nama_kategori']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Cari
                </button>
                
                <?php if (!empty($keyword) || !empty($filter_kategori)): ?>
                <a href="produk.php" class="btn" style="background: var(--gray); color: white;">
                    <i class="fas fa-times"></i> Reset
                </a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Products Table -->
        <div class="table-container">
            <div class="table-header">
                <h2><i class="fas fa-list"></i> Daftar Produk</h2>
                <span style="color: var(--gray); font-size: 14px;">
                    <?= $totalData ?> produk ditemukan
                </span>
            </div>

            <?php if (mysqli_num_rows($result) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Gambar</th>
                        <th>Nama Produk</th>
                        <th>Stok</th>
                        <th>Modal</th>
                        <th>Harga Jual</th>
                        <th>Keuntungan</th>
                        <th>Barcode</th>
                        <th>Kategori</th>
                        <th>Deskripsi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): 
                        $stok_class = $row['stok'] > 10 ? 'stok-high' : ($row['stok'] > 5 ? 'stok-medium' : 'stok-low');
                    ?>
                    <tr>
                        <td>#<?= $row['id_produk'] ?></td>
                        <td>
                            <img src="../assets/<?= htmlspecialchars($row['gambar']) ?>" 
                                 alt="<?= htmlspecialchars($row['nama_produk']) ?>" 
                                 class="product-image"
                                 onerror="this.src='../assets/default-product.png'">
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($row['nama_produk']) ?></strong>
                        </td>
                        <td>
                            <span class="stok-badge <?= $stok_class ?>">
                                <i class="fas fa-box"></i> <?= $row['stok'] ?>
                            </span>
                        </td>
                        <td>
                            <span class="price-display" style="color: var(--gray);">
                                Rp <?= number_format($row['modal'], 0, ',', '.') ?>
                            </span>
                        </td>
                        <td>
                            <span class="price-display" style="color: var(--success); font-weight: 700;">
                                Rp <?= number_format($row['harga_jual'], 0, ',', '.') ?>
                            </span>
                        </td>
                        <td>
                            <span class="price-display" style="color: var(--accent);">
                                Rp <?= number_format($row['keuntungan'], 0, ',', '.') ?>
                            </span>
                        </td>
                        <td>
                            <div class="barcode-container">
                                <img src="../service/barcode.php?text=<?= urlencode($row['barcode']) ?>&size=60&orientation=horizontal&code=Code128" 
                                     alt="Barcode" 
                                     class="barcode-img"
                                     onclick="showBarcodeModal('<?= htmlspecialchars($row['barcode']) ?>')">
                                <br>
                                <small style="font-family: 'Courier New', monospace;"><?= $row['barcode'] ?></small>
                            </div>
                        </td>
                        <td>
                            <span class="category-badge">
                                <?= htmlspecialchars($row['nama_kategori']) ?>
                            </span>
                        </td>
                        <td style="max-width: 200px;">
                            <?= htmlspecialchars(substr($row['deskripsi'], 0, 50)) ?>
                            <?= strlen($row['deskripsi']) > 50 ? '...' : '' ?>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-action btn-edit" 
                                        onclick="window.location.href='../service/edit-produk.php?id=<?= $row['id_produk'] ?>'"
                                        title="Edit Produk">
                                    <i class="fas fa-edit"></i>
                                </button>
                                
                                <button class="btn-action btn-delete" 
                                        onclick="confirmDelete(<?= $row['id_produk'] ?>, '<?= htmlspecialchars(addslashes($row['nama_produk'])) ?>')"
                                        title="Hapus Produk">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-box-open"></i>
                <h3>Tidak ada produk ditemukan</h3>
                <p><?= $keyword ? "Tidak ada hasil untuk '" . htmlspecialchars($keyword) . "'" : "Belum ada produk terdaftar" ?></p>
                <a href="../service/add-produk.php" class="btn btn-primary" style="margin-top: 20px;">
                    <i class="fas fa-plus"></i> Tambah Produk Pertama
                </a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?= $i ?>&search=<?= urlencode($keyword) ?>&kategori=<?= urlencode($filter_kategori) ?>" 
                   class="page-link <?= $i === $page ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>

        <!-- Footer -->
        <div class="footer">
            <p>&copy; <?= date('Y') ?> TimelessWatch.co - Produk Management System</p>
            <p style="font-size: 12px; margin-top: 5px;">
                <i class="fas fa-info-circle"></i> Total <?= $totalProduk ?> produk terdaftar
            </p>
        </div>
    </div>

    <!-- Barcode Modal -->
    <div id="barcodeModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); justify-content: center; align-items: center; z-index: 1000;">
        <div style="background: white; padding: 30px; border-radius: var(--border-radius); text-align: center; max-width: 400px; position: relative;">
            <button onclick="closeBarcodeModal()" style="position: absolute; top: 15px; right: 15px; background: none; border: none; font-size: 24px; cursor: pointer; color: var(--gray);">&times;</button>
            <h3 style="color: var(--primary); margin-bottom: 20px;">Barcode Produk</h3>
            <div id="modalBarcodeImg" style="margin-bottom: 20px;"></div>
            <div id="modalBarcodeText" style="font-family: 'Courier New', monospace; font-size: 18px; font-weight: bold; padding: 10px; background: var(--light); border-radius: var(--border-radius);"></div>
            <button onclick="printBarcode()" class="btn btn-primary" style="margin-top: 20px;">
                <i class="fas fa-print"></i> Cetak Barcode
            </button>
        </div>
    </div>

    <script>
        // Konfirmasi hapus
        function confirmDelete(id, name) {
            if (confirm(`Apakah Anda yakin ingin menghapus produk "${name}"?\n\nTindakan ini tidak dapat dibatalkan.`)) {
                window.location.href = `../service/delete-produk.php?id=${id}`;
            }
        }

        // Barcode Modal
        function showBarcodeModal(barcode) {
            const modal = document.getElementById('barcodeModal');
            const modalImg = document.getElementById('modalBarcodeImg');
            const modalText = document.getElementById('modalBarcodeText');
            
            modalImg.innerHTML = `<img src="../service/barcode.php?text=${encodeURIComponent(barcode)}&size=200&orientation=horizontal&code=Code128" 
                                        alt="Barcode" style="max-width: 100%;">`;
            modalText.textContent = barcode;
            modal.style.display = 'flex';
        }

        function closeBarcodeModal() {
            document.getElementById('barcodeModal').style.display = 'none';
        }

        function printBarcode() {
            const printWindow = window.open('', '_blank');
            const barcodeText = document.getElementById('modalBarcodeText').textContent;
            const barcodeImg = document.getElementById('modalBarcodeImg').innerHTML;
            
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Cetak Barcode</title>
                    <style>
                        body { font-family: Arial, sans-serif; text-align: center; padding: 20px; }
                        .barcode-container { margin: 20px 0; }
                        .barcode-text { font-family: 'Courier New', monospace; font-size: 18px; font-weight: bold; margin-top: 10px; }
                        @media print { @page { margin: 0; } body { margin: 0.5cm; } }
                    </style>
                </head>
                <body>
                    <h2>Barcode Produk</h2>
                    <div class="barcode-container">
                        ${barcodeImg}
                        <div class="barcode-text">${barcodeText}</div>
                    </div>
                    <p>TimelessWatch.co - <?= date('d/m/Y H:i:s') ?></p>
                    <script>
                        window.onload = function() { window.print(); setTimeout(() => window.close(), 1000); }
                    <\/script>
                </body>
                </html>
            `);
        }

        // Close modal on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeBarcodeModal();
            }
        });

        // Auto submit form on Enter in search
        document.getElementById('search').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                this.form.submit();
            }
        });

        // Add loading animation
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