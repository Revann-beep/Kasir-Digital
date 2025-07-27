<?php
include '../service/conection.php';

// Pagination setup
$limit = 7; // transaksi per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Simpan Transaksi
if (isset($_POST['simpan_transaksi'])) {
    $tgl_pembelian = date('Y-m-d');
    $fid_admin = $_POST['fid_admin'];
    
    $fid_member = $_POST['fid_member'];
    $total_harga = $_POST['total_harga'];

    // Simpan ke tabel transaksi
    mysqli_query($conn, "INSERT INTO transaksi 
        (tgl_pembelian, fid_admin, fid_member, total_harga)
        VALUES ('$tgl_pembelian', $fid_admin, $fid_member, $total_harga)");

    // Jika ada member aktif
    if (!empty($fid_member)) {
        $cek = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM member WHERE id_member=$fid_member AND status='aktif'"));
        if ($cek) {
            $poin = floor($total_harga / 10000);
            mysqli_query($conn, "UPDATE member SET point = point + $poin WHERE id_member = $fid_member");
            mysqli_query($conn, "INSERT INTO poin_log (id_member, poin, keterangan) VALUES ($fid_member, $poin, 'Transaksi pada $tgl_pembelian')");
        }
    }

    echo "<script>alert('Transaksi berhasil!'); window.location='transaksi.php';</script>";
    exit;
}

// Ambil total data transaksi untuk paging
$total_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM transaksi");
$total_data = mysqli_fetch_assoc($total_query)['total'];
$total_pages = ceil($total_data / $limit);

// Query data transaksi dengan limit dan offset
$query = "
    SELECT t.*, m.nama_member, p.nama_produk 
    FROM transaksi t
    LEFT JOIN member m ON t.fid_member = m.id_member
    LEFT JOIN produk p ON t.fid_produk = p.id_produk
    ORDER BY t.id_transaksi DESC
    LIMIT $limit OFFSET $offset
";
$result = mysqli_query($conn, $query);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Halaman Transaksi</title>
    <style>
        body {
            display: flex;
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f9f9f9;
        }
        .sidebar {
            width: 250px;
            background: #d4a017;
            padding: 20px;
            height: 100vh;
            color: black;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
        }
        .sidebar ul li {
            padding: 10px 0;
        }
        .sidebar ul li a {
            color: black;
            text-decoration: none;
            font-weight: bold;
        }
        .sidebar ul li a:hover {
            text-decoration: underline;
        }
        .content {
            flex: 1;
            padding: 20px;
        }
        h2 {
            margin-top: 0;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            box-shadow: 0 0 10px #ccc;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 12px 15px;
            text-align: left;
        }
        th {
            background: #f0c419;
            color: black;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background: #f7f7f7;
        }
        .pagination {
            margin-top: 20px;
            text-align: center;
        }
        .pagination a, .pagination span {
            display: inline-block;
            margin: 0 5px;
            padding: 8px 12px;
            background: #d4a017;
            color: black;
            text-decoration: none;
            font-weight: bold;
            border-radius: 4px;
        }
        .pagination a:hover {
            background: #c19b13;
        }
        .pagination .active {
            background: #a67c00;
            cursor: default;
        }
        form label, form input, form select, form button {
            display: block;
            width: 100%;
            margin-bottom: 12px;
            font-size: 14px;
        }
        form input, form select {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        form button {
            background: #28a745;
            color: white;
            border: none;
            cursor: pointer;
            font-weight: bold;
            border-radius: 4px;
            transition: background 0.3s ease;
        }
        form button:hover {
            background: #218838;
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


<div class="content">
    <h2>Halaman Transaksi</h2>

    <!-- Form Simpan Transaksi -->


    <!-- Tabel Transaksi -->
    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Tanggal</th>
                <th>Admin</th>
                
                <th>Member</th>
                <th>Total Harga</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = $offset + 1;
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>
                    <td>{$no}</td>
                    <td>{$row['tgl_pembelian']}</td>
                    <td>{$row['fid_admin']}</td>
                    
                    <td>" . ($row['nama_member'] ?? '-') . "</td>
                    <td>Rp" . number_format($row['total_harga'], 0, ',', '.') . "</td>
                </tr>";
                $no++;
            }
            ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>">&laquo; Prev</a>
        <?php endif; ?>

        <?php
        // Batasi jumlah link halaman yang ditampilkan, misalnya 5 halaman sekitar halaman aktif
        $start = max(1, $page - 2);
        $end = min($total_pages, $page + 2);

        for ($i = $start; $i <= $end; $i++):
            if ($i == $page): ?>
                <span class="active"><?= $i ?></span>
            <?php else: ?>
                <a href="?page=<?= $i ?>"><?= $i ?></a>
            <?php endif;
        endfor;
        ?>

        <?php if ($page < $total_pages): ?>
            <a href="?page=<?= $page + 1 ?>">Next &raquo;</a>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
