<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman Transaksi</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            display: flex;
            margin: 0;
            font-family: Arial, sans-serif;
        }
        .sidebar {
            width: 250px;
            background: #d4a017;
            padding: 20px;
            height: 100vh;
            color: black;
        }
        .sidebar h2 {
            font-size: 20px;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
        }
        .sidebar ul li {
            padding: 10px;
            cursor: pointer;
        }
        .sidebar ul .active {
            color: red;
        }
        .logout {
            margin-top: 20px;
            padding: 10px;
            background: white;
            border: none;
            cursor: pointer;
        }
        .content {
            flex: 1;
            padding: 20px;
        }
        .header {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 20px;
        }
        .header input {
            padding: 5px;
            width: 200px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }
        th {
            background: yellow;
        }
        .omset {
            margin-top: 10px;
            font-weight: bold;
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
    <div class="content">
        <div class="header">
            <input type="text" placeholder="Search">
        </div>
        <h2>Halaman Transaksi</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama</th>
                    <th>tgl</th>
                    <th>fid_admin</th>
                    <th>fid_produk</th>
                    <th>detail</th>
                    <th>fid_member</th>
                    <th>total_harga</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>farhan</td>
                    <td>1</td>
                    <td>1</td>
                    <td>2</td>
                    <td>Jam Garmin</td>
                    <td>1</td>
                    <td>3.999.999</td>
                </tr>
            </tbody>
        </table>
        <div class="omset">Omset: 3.999.999</div>
    </div>
</body>
</html>