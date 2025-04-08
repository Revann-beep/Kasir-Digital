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
            cursor: pointer;
        }
        .sidebar ul li.logout {
            margin-top: 50px;
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
            text-align: left;
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
            <h3>Halaman Laporan</h3>
            <button class="download-btn">+ Unduh Laporan</button>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Week</th>
                        <th>Total Penjualan</th>
                        <th>Total Transaksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>Minggu ke-1</td>
                        <td>500</td>
                        <td>500</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
