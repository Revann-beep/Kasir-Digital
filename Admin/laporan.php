<?php
include '../service/conection.php';

// Ambil data transaksi per minggu
$query = mysqli_query($conn, "
    SELECT 
        WEEK(tgl_pembelian, 1) AS minggu,
        COUNT(*) AS total_transaksi,
        SUM(total_harga) AS total_penjualan
    FROM transaksi
    GROUP BY minggu
    ORDER BY minggu ASC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman Laporan</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            display: flex;
            margin: 0;
            font-family: Arial, sans-serif;
        }
        .sidebar {
            width: 200px;
            background-color:  #b8860b;
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
        }
        .header {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 20px;
        }
        .header input {
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .report-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
        }
        .download-btn {
            background-color: #ccc;
            padding: 10px;
            border: none;
            cursor: pointer;
            margin-bottom: 10px;
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
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Toko Elektronik</h2>
        <ul>
            <li><a href="dashboard.php">⚙️ Dashboard</a></li>
            <li><a href="kategori.php">📂 Kategori</a></li>
            <li><a href="produk.php">📦 Produk</a></li>
            <li><a href="../service/index.php">↩️ Log out</a></li>
        </ul>
    </div>
    <div class="main-content">
        <div class="header">
            <input type="text" placeholder="Search">
        </div>
        <div class="report-container">
            <h3>Halaman Laporan Mingguan</h3>
            <button class="download-btn" onclick="window.print()">🖨️ Unduh Laporan</button>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Minggu ke-</th>
                        <th>Total Penjualan</th>
                        <th>Total Transaksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    while($row = mysqli_fetch_assoc($query)) {
                        echo "<tr>
                                <td>{$no}</td>
                                <td>Minggu ke-{$row['minggu']}</td>
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
