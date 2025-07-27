<?php
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
$dataQty = []; // TAMBAH INI

$data_rows = [];
while ($row = mysqli_fetch_assoc($query)) {
    $data_rows[] = $row;

    $bulan = date('F Y', strtotime($row['bulan'] . '-01'));
    $labels[] = $bulan;
    $dataKeuntungan[] = $row['total_untung'];
    $dataQty[] = $row['total_qty']; // TAMBAH INI
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Bulanan</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>

          body {
            display: flex;
            margin: 0;
            font-family: Arial, sans-serif;
        }
        .sidebar {
            width: 200px;
            background-color: #b8860b;
            padding: 20px;
            height: 200vh;
            color: white;
        }
        .sidebar h2, .sidebar ul {
            margin: 0;
            padding: 0;
        }
        .sidebar ul {
            list-style: none;
            margin-top: 20px;
        }
        .sidebar ul li {
            padding: 10px;
        }
        .sidebar ul li a {
            color: white;
            text-decoration: none;
        }
        .main-content {
            flex-grow: 1;
            padding: 20px;
            background-color: #f9f9f9;
        }
        .report-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .download-btn {
            background-color: #b8860b;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            box-shadow: 0 3px 6px rgba(184, 134, 11, 0.5);
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
            user-select: none;
            margin: 10px 0;
        }
        .download-btn:hover {
            background-color: #9a6f02;
            box-shadow: 0 5px 10px rgba(154, 111, 2, 0.7);
        }
        .download-btn:active {
            background-color: #7c5700;
            box-shadow: inset 0 3px 6px rgba(124, 87, 0, 0.8);
        }
        .filter-form {
            margin-bottom: 15px;
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .filter-form input[type="date"] {
            padding: 5px 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        .filter-form button {
            padding: 5px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table, th, td {
            border: 1px solid gray;
        }
        th, td {
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #ffcc00;
        }
        @media print {
            body {
                display: block;
            }
            .sidebar {
                display: none !important;
            }
            .filter-form, .download-btn {
                display: none !important;
            }
            .main-content {
                padding: 0;
                background-color: white;
            }
        }
        /* ... (tetap gunakan CSS yang kamu buat sebelumnya) ... */
        .chart-container {
    margin-top: 40px;
    background: white;
    border-radius: 10px;
    padding: 20px;
    max-width: 500px; /* batas lebar maksimum */
    margin-left: auto;
    margin-right: auto;
}

.chart-wrapper {
    display: flex;
    gap: 30px;
    flex-wrap: wrap;
    justify-content: center;
}

    </style>
</head>
<body>

<div class="sidebar">
    <h2>TimelessWatch.co</h2>
    <ul>
        <li><a href="dashboard.php">⚙️ Dashboard</a></li>
        <li><a href="kategori.php">📂 Kategori</a></li>
        <li><a href="produk.php" style="background: rgba(255, 255, 255, 0.2); border-radius: 5px;">📦 Produk</a></li>
        <li><a href="../service/logout.php">↩️ Log out</a></li>
    </ul>
</div>
    <!-- ... (sidebar dan filter form tetap) ... -->
    <div class="main-content">
        <div class="report-container">
            <h3>Laporan Transaksi Bulanan</h3>

            <form method="get" class="filter-form">
                <label>Dari:</label>
                <input type="date" name="start_date" value="<?= $start ?>" required>
                <label>Sampai:</label>
                <input type="date" name="end_date" value="<?= $end ?>" required>
                <button type="submit">Terapkan</button>
            </form>

            <button class="download-btn" onclick="window.print()">🖨️ Unduh Laporan</button>

            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Bulan</th>
                        <th>Total Penjualan</th>
                        <th>Total Modal</th>
                        <th>Total Keuntungan</th>
                        <th>Total Transaksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    foreach ($data_rows as $row) {
                        $bulanDate = strtotime($row['bulan'] . '-01');
                        $bulanFormatted = date('F Y', $bulanDate);

                        echo "<tr>
                            <td>{$no}</td>
                            <td>{$bulanFormatted}</td>
                            <td>Rp" . number_format($row['total_penjualan'], 0, ',', '.') . "</td>
                            <td>Rp" . number_format($row['total_modal'], 0, ',', '.') . "</td>
                            <td>Rp" . number_format($row['total_untung'], 0, ',', '.') . "</td>
                            <td>{$row['total_transaksi']}</td>
                        </tr>";
                        $no++;
                    }
                    ?>
                </tbody>
            </table>

            <!-- Grafik Bagan -->
            <!-- Grafik Bagan -->
<div class="chart-wrapper">
    <div class="chart-container">
        <h4>Grafik Batang: Total Keuntungan per Bulan</h4>
        <canvas id="chartKeuntungan" width="600" height="300"></canvas>
    </div>
    
</div>

        </div>
    </div>

    <script>
        // Grafik Batang: Keuntungan
const ctx = document.getElementById('chartKeuntungan').getContext('2d');
const chart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_reverse($labels)) ?>,
        datasets: [{
            label: 'Total Keuntungan (Rp)',
            data: <?= json_encode(array_reverse($dataKeuntungan)) ?>,
            backgroundColor: '#f39c12'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            title: {
                display: true,
                text: 'Keuntungan Bulanan (Rp)'
            }
        },
        scales: {
            y: {
                ticks: {
                    callback: function(value) {
                        return 'Rp' + value.toLocaleString('id-ID');
                    }
                }
            }
        }
    }
});

// Diagram Lingkaran: Jumlah Produk Terjual



    </script>
</body>
</html>
