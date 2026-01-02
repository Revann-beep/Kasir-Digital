<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../service/index.php");
    exit;
}

// Alert selamat datang (sekali setelah login)
if (isset($_SESSION['welcome_message'])) {
    echo "<script>alert('" . $_SESSION['welcome_message'] . "');</script>";
    unset($_SESSION['welcome_message']);
}

require_once '../service/conection.php';

$username = $_SESSION['username'];

// Ambil gambar jika belum ada di session
if (!isset($_SESSION['gambar'])) {
    $queryGambar = mysqli_query($conn, "SELECT gambar FROM admin WHERE username = '$username'");
    $resultGambar = mysqli_fetch_assoc($queryGambar);
    $_SESSION['gambar'] = $resultGambar['gambar'] ?? 'default.jpg';
}

$labels = [];
$data = [];
$filter_aktif = '';

// Tentukan jenis filter yang aktif
if (!empty($_GET['mingguan']) && $_GET['mingguan'] !== 'general') {
    $judul_label = 'Mingguan';
    $filter_aktif = 'mingguan';
} elseif (!empty($_GET['bulanan']) && $_GET['bulanan'] !== 'general') {
    $judul_label = 'Bulanan';
    $filter_aktif = 'bulanan';
} elseif (!empty($_GET['tahunan']) && $_GET['tahunan'] !== 'general') {
    $judul_label = 'Tahunan';
    $filter_aktif = 'tahunan';
} else {
    $judul_label = '7 Hari Terakhir';
    $filter_aktif = 'default';
}

// Reset filter lainnya jika ada yang dipilih
if (!empty($_GET['mingguan']) && $_GET['mingguan'] !== 'general') {
    // FILTER MINGGUAN (format: minggu|tahun)
    list($minggu, $tahun) = explode('|', $_GET['mingguan']);
    $query = mysqli_query($conn, "
        SELECT 
            DATE(t.tgl_pembelian) AS tanggal,
            SUM(d.qty) AS total_item
        FROM transaksi t
        JOIN detail_transaksi d ON t.id_transaksi = d.fid_transaksi
        WHERE 
            WEEK(t.tgl_pembelian, 1) = '$minggu' 
            AND YEAR(t.tgl_pembelian) = '$tahun'
        GROUP BY DATE(t.tgl_pembelian)
        ORDER BY DATE(t.tgl_pembelian)
    ");
    
    // Format label menjadi nama hari
    while ($row = mysqli_fetch_assoc($query)) {
        $tanggal = new DateTime($row['tanggal']);
        $labels[] = $tanggal->format('D, d M'); // Contoh: Sen, 15 Apr
        $data[] = (int)$row['total_item'];
    }
    
} elseif (!empty($_GET['bulanan']) && $_GET['bulanan'] !== 'general') {
    // FILTER BULANAN (format: bulan|tahun)
    list($bulan, $tahun) = explode('|', $_GET['bulanan']);
    $query = mysqli_query($conn, "
        SELECT 
            WEEK(t.tgl_pembelian, 1) AS minggu_ke,
            SUM(d.qty) AS total_item
        FROM transaksi t
        JOIN detail_transaksi d ON t.id_transaksi = d.fid_transaksi
        WHERE 
            MONTH(t.tgl_pembelian) = '$bulan' 
            AND YEAR(t.tgl_pembelian) = '$tahun'
        GROUP BY WEEK(t.tgl_pembelian, 1)
        ORDER BY WEEK(t.tgl_pembelian, 1)
    ");
    
    // Format label menjadi "Minggu ke-X"
    while ($row = mysqli_fetch_assoc($query)) {
        $labels[] = "Minggu ke-" . $row['minggu_ke'];
        $data[] = (int)$row['total_item'];
    }
    
} elseif (!empty($_GET['tahunan']) && $_GET['tahunan'] !== 'general') {
    // FILTER TAHUNAN
    $tahun = $_GET['tahunan'];
    $query = mysqli_query($conn, "
        SELECT 
            MONTH(t.tgl_pembelian) AS bulan,
            SUM(d.qty) AS total_item
        FROM transaksi t
        JOIN detail_transaksi d ON t.id_transaksi = d.fid_transaksi
        WHERE YEAR(t.tgl_pembelian) = '$tahun'
        GROUP BY MONTH(t.tgl_pembelian)
        ORDER BY MONTH(t.tgl_pembelian)
    ");
    
    // Format label menjadi nama bulan
    $nama_bulan = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    
    while ($row = mysqli_fetch_assoc($query)) {
        $labels[] = $nama_bulan[$row['bulan']];
        $data[] = (int)$row['total_item'];
    }
    
} else {
    // DEFAULT: 7 HARI TERAKHIR
    $query = mysqli_query($conn, "
        SELECT 
            DATE(t.tgl_pembelian) AS tanggal,
            SUM(d.qty) AS total_item
        FROM transaksi t
        JOIN detail_transaksi d ON t.id_transaksi = d.fid_transaksi
        WHERE t.tgl_pembelian >= CURDATE() - INTERVAL 6 DAY
        GROUP BY DATE(t.tgl_pembelian)
        ORDER BY DATE(t.tgl_pembelian)
    ");
    
    // Format label menjadi "Hari, Tanggal"
    while ($row = mysqli_fetch_assoc($query)) {
        $tanggal = new DateTime($row['tanggal']);
        $labels[] = $tanggal->format('D, d M'); // Contoh: Sen, 15 Apr
        $data[] = (int)$row['total_item'];
    }
}

// Jika tidak ada data, tampilkan pesan
if (empty($data)) {
    $labels = ['Tidak ada data'];
    $data = [0];
}

// Ambil statistik untuk card
$query_total_transaksi = mysqli_query($conn, "
    SELECT COUNT(*) as total FROM transaksi 
    WHERE DATE(tgl_pembelian) = CURDATE()
");
$total_transaksi_hari_ini = mysqli_fetch_assoc($query_total_transaksi)['total'] ?? 0;

$query_total_pendapatan = mysqli_query($conn, "
    SELECT SUM(total_bayar) as total FROM transaksi 
    WHERE DATE(tgl_pembelian) = CURDATE()
");
$total_pendapatan_hari_ini = mysqli_fetch_assoc($query_total_pendapatan)['total'] ?? 0;

$query_total_produk = mysqli_query($conn, "
    SELECT SUM(qty) as total FROM detail_transaksi dt
    JOIN transaksi t ON dt.fid_transaksi = t.id_transaksi
    WHERE DATE(t.tgl_pembelian) = CURDATE()
");
$total_produk_hari_ini = mysqli_fetch_assoc($query_total_produk)['total'] ?? 0;

$query_total_member = mysqli_query($conn, "
    SELECT COUNT(*) as total FROM member WHERE status = 'aktif'
");
$total_member_aktif = mysqli_fetch_assoc($query_total_member)['total'] ?? 0;

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #1a237e;
            --primary-light: #534bae;
            --secondary: #ff9800;
            --accent: #00bcd4;
            --success: #4caf50;
            --light: #f5f5f5;
            --dark: #212121;
            --gray: #757575;
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
            background: linear-gradient(180deg, var(--primary) 0%, #0d47a1 100%);
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
        }

        .logo p {
            font-size: 12px;
            opacity: 0.8;
            margin-top: 5px;
        }

        .sidebar-nav ul {
            list-style: none;
            flex-grow: 1;
        }

        .sidebar-nav ul li {
            margin-bottom: 15px;
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

        .sidebar-nav ul li a:hover,
        .sidebar-nav ul li a.active {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            transform: translateX(5px);
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
            padding: 20px 30px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin-bottom: 30px;
        }

        .page-title h1 {
            font-size: 28px;
            color: var(--primary);
            font-weight: 700;
        }

        .page-title p {
            color: var(--gray);
            font-size: 14px;
            margin-top: 5px;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--primary);
            box-shadow: 0 0 0 3px rgba(26, 35, 126, 0.1);
            transition: var(--transition);
        }

        .user-avatar:hover {
            transform: scale(1.05);
            box-shadow: 0 0 0 3px rgba(26, 35, 126, 0.2);
        }

        .user-info h3 {
            font-weight: 600;
            font-size: 16px;
            color: var(--dark);
        }

        .user-info p {
            font-size: 13px;
            color: var(--gray);
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
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
        .stat-card:nth-child(4) .stat-icon { background: var(--accent); }

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

        /* Chart Section */
        .chart-section {
            display: grid;
            grid-template-columns: 1fr;
            gap: 30px;
            margin-bottom: 40px;
        }

        .chart-container {
            background: white;
            border-radius: var(--border-radius);
            padding: 30px;
            box-shadow: var(--box-shadow);
            height: 400px;
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .chart-header h2 {
            font-size: 20px;
            color: var(--primary);
            font-weight: 600;
        }

        .chart-header p {
            color: var(--gray);
            font-size: 14px;
        }

        /* Filter Form */
        .filter-form {
            background: white;
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--box-shadow);
            margin-bottom: 30px;
        }

        .filter-form h3 {
            font-size: 18px;
            color: var(--primary);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .filter-row {
            display: flex;
            gap: 20px;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .filter-group label {
            font-weight: 600;
            color: var(--dark);
            font-size: 14px;
        }

        .filter-select {
            padding: 10px 15px;
            border-radius: 8px;
            border: 2px solid #e0e0e0;
            font-size: 14px;
            cursor: pointer;
            transition: var(--transition);
            background: white;
            min-width: 200px;
        }

        .filter-select:hover,
        .filter-select:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(26, 35, 126, 0.1);
        }

        .btn-reset {
            padding: 10px 20px;
            background: var(--gray);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-reset:hover {
            background: #616161;
            transform: translateY(-2px);
        }

        /* Footer */
        .footer {
            margin-top: auto;
            text-align: center;
            padding: 20px;
            color: var(--gray);
            font-size: 14px;
            border-top: 1px solid #e0e0e0;
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
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }
            .filter-row {
                flex-direction: column;
                align-items: flex-start;
            }
            .filter-select {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            .chart-container {
                padding: 20px;
                height: 300px;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <h2>TimelessWatch.co</h2>
            <p>Admin Dashboard</p>
        </div>
        
        <nav class="sidebar-nav">
            <ul>
                <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="kategori.php"><i class="fas fa-tags"></i> Kategori</a></li>
                <li><a href="produk.php"><i class="fas fa-box-open"></i> Produk</a></li>
                <li><a href="member.php"><i class="fas fa-users"></i> Member</a></li>
                <li><a href="transaksi.php"><i class="fas fa-receipt"></i> Transaksi</a></li>
                <li><a href="laporan.php"><i class="fas fa-chart-bar"></i> Laporan</a></li>
                <li><a href="admin.php"><i class="fas fa-user-cog"></i> Admin</a></li>
                <li><a href="../service/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="header">
            <div class="page-title">
                <h1>Dashboard Admin</h1>
                <p>Selamat datang kembali, <?php echo htmlspecialchars($_SESSION['username']); ?>! 👋</p>
            </div>
            
            <div class="user-profile">
                <img src="../assets/<?php echo htmlspecialchars($_SESSION['gambar']); ?>" alt="User Avatar" class="user-avatar">
                <div class="user-info">
                    <h3><?php echo htmlspecialchars($_SESSION['username']); ?></h3>
                    <p>Administrator</p>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-value"><?php echo number_format($total_transaksi_hari_ini); ?></div>
                <div class="stat-label">Transaksi Hari Ini</div>
            </div>
            
            <div class="stat-card orange">
                <div class="stat-icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="stat-value">Rp <?php echo number_format($total_pendapatan_hari_ini); ?></div>
                <div class="stat-label">Pendapatan Hari Ini</div>
            </div>
            
            <div class="stat-card green">
                <div class="stat-icon">
                    <i class="fas fa-boxes"></i>
                </div>
                <div class="stat-value"><?php echo number_format($total_produk_hari_ini); ?></div>
                <div class="stat-label">Produk Terjual Hari Ini</div>
            </div>
            
            <div class="stat-card cyan">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-value"><?php echo number_format($total_member_aktif); ?></div>
                <div class="stat-label">Member Aktif</div>
            </div>
        </div>

        <!-- Filter Form -->
        <div class="filter-form">
            <h3><i class="fas fa-filter"></i> Filter Grafik Penjualan</h3>
            
            <form method="GET" class="filter-row">
                <div class="filter-group">
                    <label for="mingguan">Mingguan:</label>
                    <select name="mingguan" id="mingguan" class="filter-select" onchange="this.form.submit()">
                        <option value="">Pilih Minggu</option>
                        <?php
                        $mingguQuery = mysqli_query($conn, "
                            SELECT DISTINCT 
                                WEEK(tgl_pembelian, 1) AS minggu, 
                                YEAR(tgl_pembelian) AS tahun 
                            FROM transaksi 
                            ORDER BY tahun DESC, minggu DESC
                            LIMIT 20
                        ");
                        while ($row = mysqli_fetch_assoc($mingguQuery)) {
                            $value = $row['minggu'] . '|' . $row['tahun'];
                            $label = "Minggu ke-" . $row['minggu'] . " (" . $row['tahun'] . ")";
                            $selected = (isset($_GET['mingguan']) && $_GET['mingguan'] === $value) ? 'selected' : '';
                            echo "<option value=\"$value\" $selected>$label</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="bulanan">Bulanan:</label>
                    <select name="bulanan" id="bulanan" class="filter-select" onchange="this.form.submit()">
                        <option value="">Pilih Bulan</option>
                        <?php
                        $bulanQuery = mysqli_query($conn, "
                            SELECT DISTINCT 
                                MONTH(tgl_pembelian) AS bulan, 
                                YEAR(tgl_pembelian) AS tahun 
                            FROM transaksi 
                            ORDER BY tahun DESC, bulan DESC
                        ");
                        while ($row = mysqli_fetch_assoc($bulanQuery)) {
                            $value = $row['bulan'] . '|' . $row['tahun'];
                            $bulanNama = date('F', mktime(0, 0, 0, $row['bulan'], 10));
                            $label = $bulanNama . " " . $row['tahun'];
                            $selected = (isset($_GET['bulanan']) && $_GET['bulanan'] === $value) ? 'selected' : '';
                            echo "<option value=\"$value\" $selected>$label</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="tahunan">Tahunan:</label>
                    <select name="tahunan" id="tahunan" class="filter-select" onchange="this.form.submit()">
                        <option value="">Pilih Tahun</option>
                        <?php
                        $tahunQuery = mysqli_query($conn, "
                            SELECT DISTINCT YEAR(tgl_pembelian) AS tahun 
                            FROM transaksi 
                            ORDER BY tahun DESC
                        ");
                        while ($row = mysqli_fetch_assoc($tahunQuery)) {
                            $value = $row['tahun'];
                            $label = $row['tahun'];
                            $selected = (isset($_GET['tahunan']) && $_GET['tahunan'] === $value) ? 'selected' : '';
                            echo "<option value=\"$value\" $selected>$label</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <?php if ($filter_aktif !== 'default'): ?>
                <button type="button" class="btn-reset" onclick="window.location.href='dashboard.php'">
                    <i class="fas fa-times"></i> Reset Filter
                </button>
                <?php endif; ?>
            </form>
        </div>

        <!-- Chart Section -->
        <div class="chart-section">
            <div class="chart-container">
                <div class="chart-header">
                    <div>
                        <h2>Grafik Penjualan</h2>
                        <p><?php echo $judul_label; ?> - Data Jumlah Item Terjual</p>
                    </div>
                    <div id="currentDate" class="date"></div>
                </div>
                
                <canvas id="chartData"></canvas>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>&copy; <?php echo date('Y'); ?> TimelessWatch.co - All rights reserved</p>
            <p>Dashboard Admin v1.0 | Last updated: <?php echo date('d/m/Y H:i:s'); ?></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Initialize Chart
        const ctx = document.getElementById('chartData').getContext('2d');
        
        // Color gradient
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(26, 35, 126, 0.8)');
        gradient.addColorStop(1, 'rgba(26, 35, 126, 0.1)');
        
        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: [{
                    label: 'Jumlah Item Terjual',
                    data: <?php echo json_encode($data); ?>,
                    backgroundColor: gradient,
                    borderColor: 'rgba(26, 35, 126, 1)',
                    borderWidth: 2,
                    borderRadius: 6,
                    hoverBackgroundColor: 'rgba(255, 152, 0, 0.8)',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            font: {
                                size: 14,
                                family: "'Segoe UI', sans-serif"
                            },
                            padding: 20
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.7)',
                        titleFont: {
                            size: 14
                        },
                        bodyFont: {
                            size: 14
                        },
                        padding: 12,
                        cornerRadius: 6
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            font: {
                                size: 12
                            },
                            stepSize: 1,
                            callback: function(value) {
                                return value + ' item';
                            }
                        },
                        title: {
                            display: true,
                            text: 'Jumlah Item',
                            font: {
                                size: 14,
                                weight: 'bold'
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 12
                            },
                            maxRotation: 45
                        },
                        title: {
                            display: true,
                            text: '<?php echo $judul_label; ?>',
                            font: {
                                size: 14,
                                weight: 'bold'
                            }
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                animation: {
                    duration: 1000,
                    easing: 'easeOutQuart'
                }
            }
        });

        // Update current date
        function updateDate() {
            const dateElement = document.getElementById('currentDate');
            const options = { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            };
            const today = new Date().toLocaleDateString('id-ID', options);
            dateElement.textContent = today;
        }
        updateDate();
        
        // Update date every minute
        setInterval(updateDate, 60000);
        
        // Auto submit form when filter changes (already handled by onchange)
    </script>
</body>
</html>