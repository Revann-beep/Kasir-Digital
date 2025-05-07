<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../service/index.php");
    exit;
}

include '../service/conection.php'; // pastikan file conn

// Jumlah item terjual per hari (7 hari terakhir)
$harianQuery = mysqli_query($conn, "
    SELECT DATE(t.tgl_pembelian) AS tanggal, SUM(d.jumlah) AS total_item
    FROM transaksi t
    JOIN detail_transaksi d ON t.id_transaksi = d.fid_transaksi
    WHERE t.tgl_pembelian >= CURDATE() - INTERVAL 6 DAY
    GROUP BY DATE(t.tgl_pembelian)
    ORDER BY DATE(t.tgl_pembelian)
");

$hariLabels = [];
$hariData = [];
while ($row = mysqli_fetch_assoc($harianQuery)) {
    $hariLabels[] = $row['tanggal'];
    $hariData[] = (int)$row['total_item'];
}


// Jumlah item terjual per bulan (12 bulan terakhir)
$bulananQuery = mysqli_query($conn, "
    SELECT DATE_FORMAT(t.tgl_pembelian, '%b %Y') AS bulan, SUM(d.jumlah) AS total_item
    FROM transaksi t
    JOIN detail_transaksi d ON t.id_transaksi = d.fid_transaksi
    WHERE t.tgl_pembelian >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY YEAR(t.tgl_pembelian), MONTH(t.tgl_pembelian)
    ORDER BY YEAR(t.tgl_pembelian), MONTH(t.tgl_pembelian)
");

$bulanLabels = [];
$bulanData = [];
while ($row = mysqli_fetch_assoc($bulananQuery)) {
    $bulanLabels[] = $row['bulan'];
    $bulanData[] = (int)$row['total_item'];
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
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
            padding: 0;
        }
        .sidebar ul li {
            padding: 10px 0;
        }
        .sidebar ul li a {
            color: white;
            text-decoration: none;
            display: block;
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
    margin-left: auto; /* Mendorong elemen ke kanan */
}

.user-icon {
    width: 40px; /* Ukuran gambar */
    height: 40px;
    border-radius: 50%; /* Agar menjadi lingkaran */
    margin-right: 10px; /* Jarak antara foto dan nama */
}

.username {
    font-weight: bold;
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
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            width: 100%;
        }
        .charts {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        .chart {
            width: 48%;
            height: 300px;
            background: white;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>


    <div class="sidebar">
        <h2>TimelessWatch.co
</h2>
        <ul>
            <li><a href="dashboard.php">⚙️ Dashboard</a></li>
            <li><a href="kategori.php">📂 Kategori</a></li>
            <li><a href="produk.php">📦 Produk</a></li>
            <li><a href="../service/index.php">↩️ Log out</a></li>
        </ul>
    </div>
    <div class="main-content">
    <header>
    <span class="date" id="currentDate"></span>
    <div class="account-info">
        <a href="profile.php" style="display: flex; align-items: center; text-decoration: none; color: black;">
            <img src="../assets/admin.jpg" alt="user" class="user-icon">
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
        <div class="charts">
            <div class="chart">
                <canvas id="chartBulanan"></canvas>
            </div>
            <div class="chart">
                <canvas id="chartTahunan"></canvas>
            </div>
        </div>
    </div>
    <script>
    // Grafik Harian
const ctxHarian = document.getElementById('chartBulanan').getContext('2d');
new Chart(ctxHarian, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($hariLabels); ?>,
        datasets: [{
            label: 'Jumlah Item Terjual per Hari',
            data: <?php echo json_encode($hariData); ?>,
            backgroundColor: 'rgba(75, 192, 192, 0.5)'
        }]
    }
});

// Grafik Bulanan
const ctxBulanan = document.getElementById('chartTahunan').getContext('2d');
new Chart(ctxBulanan, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($bulanLabels); ?>,
        datasets: [{
            label: 'Jumlah Item Terjual per Bulan',
            data: <?php echo json_encode($bulanData); ?>,
            backgroundColor: 'rgba(153, 102, 255, 0.5)'
        }]
    }
});


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
