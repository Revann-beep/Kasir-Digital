<?php
include '../service/conection.php';

// Tangkap filter tanggal dari form
$start = $_GET['start_date'] ?? date('Y-m-01', strtotime('-2 month')); // default: 2 bulan lalu
$end   = $_GET['end_date'] ?? date('Y-m-d');

// Query data transaksi per bulan
$query = mysqli_query($conn, "
    SELECT 
        DATE_FORMAT(tgl_pembelian, '%Y-%m') AS bulan,
        COUNT(*) AS total_transaksi,
        SUM(total_harga) AS total_penjualan
    FROM transaksi
    WHERE DATE(tgl_pembelian) BETWEEN '$start' AND '$end'
    GROUP BY bulan
    ORDER BY bulan ASC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Laporan Bulanan</title>
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
            height: 100vh;
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
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .header input[type="text"] {
            padding: 5px 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 200px;
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
            .header, .filter-form, .download-btn {
                display: none !important;
            }
            .main-content {
                padding: 0;
                background-color: white;
            }
            table {
                page-break-inside: auto;
            }
            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Toko Elektronik</h2>
        <ul>
            <li><a href="dashboard.php">⚙️ Dashboard</a></li>
            <li><a href="kategori.php">📂 Kategori</a></li>
            <li><a href="produk.php">📦 Produk</a></li>
            <li><a href="../service/logout.php">↩️ Log out</a></li>
        </ul>
    </div>
    <div class="main-content">
        ch
        <div class="report-container">
            <h3>Laporan Transaksi Bulanan</h3>

            <!-- Filter kalender -->
            <form method="get" class="filter-form">
                <label>Dari:</label>
                <input type="date" name="start_date" value="<?= $start ?>" required>
                <label>Sampai:</label>
                <input type="date" name="end_date" value="<?= $end ?>" required>
                <button type="submit">Terapkan</button>
            </form>

            <button class="download-btn" onclick="window.print()">
                <span class="icon">🖨️</span> Unduh Laporan
            </button>

            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Bulan</th>
                        <th>Total Penjualan</th>
                        <th>Total Transaksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    while($row = mysqli_fetch_assoc($query)) {
                        $bulanDate = strtotime($row['bulan'] . '-01');
                        $bulanAwal = date('Y-m-01', $bulanDate);
                        $bulanAkhir = date('Y-m-t', $bulanDate);

                        $rentangAwal = max($start, $bulanAwal);
                        $rentangAkhir = min($end, $bulanAkhir);

                        $rentangAwalFormatted = date('d-m-Y', strtotime($rentangAwal));
                        $rentangAkhirFormatted = date('d-m-Y', strtotime($rentangAkhir));

                        $bulanFormatted = date('F Y', $bulanDate) . " ({$rentangAwalFormatted} s.d. {$rentangAkhirFormatted})";

                        echo "<tr>
                                <td>{$no}</td>
                                <td>{$bulanFormatted}</td>
                                <td>Rp" . number_format($row['total_penjualan'], 0, ',', '.') . "</td>
                                <td>{$row['total_transaksi']}</td>
                              </tr>";
                        $no++;
                    } 
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
