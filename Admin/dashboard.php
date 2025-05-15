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
    <meta charset="UTF-8" />
    <title>Dashboard Admin</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0; padding: 0; box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            display: flex;
            height: 100vh;
            background: #f0f2f5;
            color: #333;
        }
        .sidebar {
            width: 260px;
            background: #b8860b;
            padding: 30px 20px;
            color: white;
            display: flex;
            flex-direction: column;
        }
        .sidebar h2 {
            margin-bottom: 30px;
            font-weight: 700;
            letter-spacing: 1.2px;
            font-size: 24px;
        }
        .sidebar ul {
            list-style: none;
            flex-grow: 1;
        }
        .sidebar ul li {
            margin-bottom: 20px;
        }
        .sidebar ul li a {
            color: white;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: color 0.3s ease;
        }
        .sidebar ul li a:hover {
            color: #fffacd;
        }
        .main-content {
            flex: 1;
            padding: 30px 40px;
            display: flex;
            flex-direction: column;
        }
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            padding: 15px 25px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgb(0 0 0 / 0.1);
            margin-bottom: 30px;
        }
        .date {
            font-size: 18px;
            font-weight: 600;
            color: #555;
        }
        .account-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .user-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #b8860b;
            transition: transform 0.3s ease;
        }
        .user-icon:hover {
            transform: scale(1.1);
            border-color: #ffca28;
        }
        .username {
            font-weight: 700;
            font-size: 16px;
            color: #333;
        }

        /* Grid untuk tombol navigasi */
        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .grid-container a {
            text-decoration: none;
        }
        .grid-container button {
            background: white;
            border: 2px solid #b8860b;
            color: #b8860b;
            font-weight: 700;
            font-size: 16px;
            padding: 18px 12px;
            border-radius: 12px;
            cursor: pointer;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 8px rgb(184 134 11 / 0.2);
            user-select: none;
        }
        .grid-container button:hover {
            background: #b8860b;
            color: white;
            box-shadow: 0 6px 12px rgb(184 134 11 / 0.4);
            transform: translateY(-3px);
        }
        .grid-container button svg {
            font-size: 20px;
        }

        form {
            margin-bottom: 25px;
            font-weight: 600;
            color: #444;
        }
        label {
            margin-right: 15px;
            font-size: 16px;
        }
        select {
            padding: 7px 12px;
            border-radius: 8px;
            border: 1.5px solid #b8860b;
            font-size: 16px;
            cursor: pointer;
            transition: border-color 0.3s ease;
        }
        select:hover,
        select:focus {
            border-color: #ffca28;
            outline: none;
        }

        .chart {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 6px 15px rgb(0 0 0 / 0.1);
            height: 420px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        @media (max-width: 768px) {
            body {
                flex-direction: column;
                height: auto;
            }
            .sidebar {
                width: 100%;
                flex-direction: row;
                justify-content: space-between;
                padding: 15px 20px;
                align-items: center;
            }
            .sidebar h2 {
                margin: 0;
                font-size: 20px;
            }
            .sidebar ul {
                display: flex;
                gap: 15px;
                margin: 0;
            }
            .sidebar ul li {
                margin: 0;
            }
            .main-content {
                padding: 20px;
            }
            .grid-container {
                grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            }
            header {
                flex-direction: column;
                gap: 15px;
                padding: 20px;
                text-align: center;
            }
            .account-info {
                justify-content: center;
            }
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
                <a href="profile.php" style="display: flex; align-items: center; text-decoration: none; color: inherit;">
                    <img src="../assets/<?php echo htmlspecialchars($_SESSION['gambar']); ?>" alt="user" class="user-icon" />
                    <span class="username"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                </a>
            </div>
        </header>

        <div class="grid-container" role="navigation" aria-label="Menu Navigasi">
            <a href="produk.php"><button type="button">📦 Data Product</button></a>
            <a href="laporan.php"><button type="button">📊 Laporan</button></a>
            <a href="member.php"><button type="button">👥 Halaman Member</button></a>
            <a href="admin.php"><button type="button">👨‍💼 Admin</button></a>
            <a href="transaksi.php"><button type="button">💳 Transaksi</button></a>
        </div>

        <form method="GET" aria-label="Filter Grafik">
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
                    backgroundColor: 'rgba(184, 134, 11, 0.7)'
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
                },
                responsive: true,
                maintainAspectRatio: false,
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
