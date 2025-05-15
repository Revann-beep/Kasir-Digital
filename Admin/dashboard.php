<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../service/index.php");
    exit;
}

include '../service/conection.php';

$username = $_SESSION['username'];

// Ambil gambar user jika belum disimpan di session
if (!isset($_SESSION['gambar'])) {
    $queryGambar = mysqli_query($conn, "SELECT gambar FROM admin WHERE username = '$username'");
    $resultGambar = mysqli_fetch_assoc($queryGambar);
    $_SESSION['gambar'] = $resultGambar['gambar'] ?? 'default.jpg'; // fallback default
}

$filter = $_GET['filter'] ?? 'harian';

// Query sesuai filter
if ($filter === 'harian') {
    $query = mysqli_query($conn, "
        SELECT DATE(t.tgl_pembelian) AS label, SUM(d.jumlah) AS total_item
        FROM transaksi t
        JOIN detail_transaksi d ON t.id_transaksi = d.fid_transaksi
        WHERE t.tgl_pembelian >= CURDATE() - INTERVAL 6 DAY
        GROUP BY DATE(t.tgl_pembelian)
        ORDER BY DATE(t.tgl_pembelian)
    ");
} elseif ($filter === 'bulanan') {
    $query = mysqli_query($conn, "
        SELECT DATE_FORMAT(t.tgl_pembelian, '%b %Y') AS label, SUM(d.jumlah) AS total_item
        FROM transaksi t
        JOIN detail_transaksi d ON t.id_transaksi = d.fid_transaksi
        WHERE t.tgl_pembelian >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
        GROUP BY YEAR(t.tgl_pembelian), MONTH(t.tgl_pembelian)
        ORDER BY YEAR(t.tgl_pembelian), MONTH(t.tgl_pembelian)
    ");
} else { // tahunan
    $query = mysqli_query($conn, "
        SELECT YEAR(t.tgl_pembelian) AS label, SUM(d.jumlah) AS total_item
        FROM transaksi t
        JOIN detail_transaksi d ON t.id_transaksi = d.fid_transaksi
        GROUP BY YEAR(t.tgl_pembelian)
        ORDER BY YEAR(t.tgl_pembelian)
    ");
}

$labels = [];
$data = [];
while ($row = mysqli_fetch_assoc($query)) {
    $labels[] = $row['label'];
    $data[] = (int)$row['total_item'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0; padding: 0; box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        body {
            display: flex;
            height: 100vh;
        }
        .sidebar {
            width: 250px;
            background: #b8860b;
            padding: 20px;
            color: white;
        }
        .sidebar h2 {
            margin-bottom: 20px;
        }
        .sidebar ul {
            list-style: none;
        }
        .sidebar ul li {
            padding: 10px 0;
        }
        .sidebar ul li a {
            color: white;
            text-decoration: none;
        }
        .main-content {
            flex: 1;
            padding: 20px;
            background: #f4f4f4;
        }
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .account-info {
            display: flex;
            align-items: center;
            margin-left: auto;
        }
        .user-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
            object-fit: cover;
        }
        .username {
            font-weight: bold;
        }
        form {
            margin-bottom: 20px;
        }
        label {
            font-weight: bold;
            margin-right: 10px;
        }
        select {
            padding: 5px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        .grid-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }
        .grid-container a {
            text-decoration: none;
        }
        .grid-container button {
            background: white;
            border: none;
            padding: 15px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .chart {
            width: 100%;
            height: 400px;
            background: white;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>TimelessWatch.co</h2>
        <ul>
            <li><a href="dashboard.php">⚙️ Dashboard</a></li>
            <li><a href="kategori.php">📂 Kategori</a></li>
            <li><a href="produk.php">📦 Produk</a></li>
            <li><a href="../service/logout.php">↩️ Log out</a></li>
        </ul>
    </div>
    <div class="main-content">
        <header>
            <span class="date" id="currentDate"></span>
            <div class="account-info">
                <a href="profile.php" style="display: flex; align-items: center; text-decoration: none; color: black;">
                    <img src="../assets/<?php echo htmlspecialchars($_SESSION['gambar']); ?>" alt="user" class="user-icon">
                    <span class="username"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                </a>
            </div>
        </header>


        <div class="grid-container">
            <a href="produk.php"><button>📦 Data Product</button></a>
            <a href="laporan.php"><button>📊 Laporan</button></a>
            <a href="member.php"><button>👥 Halaman Member</button></a>
            <a href="../user/keranjang.php"><button>🛒 Keranjang</button></a>
            <a href="admin.php"><button>👨‍💼 Admin</button></a>
            <a href="transaksi.php"><button>💳 Transaksi</button></a>
        </div>

        <form method="GET">
            <label for="filter">Lihat grafik berdasarkan:</label>
            <select name="filter" id="filter" onchange="this.form.submit()">
                <option value="harian" <?php if ($filter === 'harian') echo 'selected'; ?>>Harian</option>
                <option value="bulanan" <?php if ($filter === 'bulanan') echo 'selected'; ?>>Bulanan</option>
                <option value="tahunan" <?php if ($filter === 'tahunan') echo 'selected'; ?>>Tahunan</option>
            </select>
        </form>

        <div class="chart">
            <canvas id="chartData"></canvas>
        </div>

        
    </div>

    <script>
        const ctx = document.getElementById('chartData').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: [{
                    label: 'Jumlah Item Terjual (<?php echo ucfirst($filter); ?>)',
                    data: <?php echo json_encode($data); ?>,
                    backgroundColor: 'rgba(75, 192, 192, 0.5)'
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // Tampilkan tanggal hari ini
        function updateDate() {
            const dateElement = document.getElementById('currentDate');
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const today = new Date().toLocaleDateString('id-ID', options);
            dateElement.textContent = today;
        }
        updateDate();
    </script>
</body>
</html>
