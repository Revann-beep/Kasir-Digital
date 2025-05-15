<?php
include '../service/conection.php';

// Simpan Transaksi
if (isset($_POST['simpan_transaksi'])) {
    $tgl_pembelian = date('Y-m-d');
    $fid_admin = $_POST['fid_admin'];
    $fid_produk = $_POST['fid_produk'];
    $detail = $_POST['detail'];
    $fid_member = $_POST['fid_member'];
    $total_harga = $_POST['total_harga'];

    // Simpan ke tabel transaksi
    mysqli_query($conn, "INSERT INTO transaksi 
        (tgl_pembelian, fid_admin, fid_produk, detail, fid_member, total_harga)
        VALUES ('$tgl_pembelian', $fid_admin, $fid_produk, '$detail', $fid_member, $total_harga)");

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
}
?>

<!-- HTML -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Halaman Transaksi</title>
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
        .sidebar ul { list-style: none; padding: 0; }
        .sidebar ul li { padding: 10px; }
        .content { flex: 1; padding: 20px; }
        .header input { padding: 5px; width: 200px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; }
        th { background: yellow; }
        form input, form button, form select { padding: 8px; width: 100%; margin-bottom: 10px; }
        form button { background: green; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>
<div class="sidebar">
    <h2>Toko Elektronik</h2>
    <ul>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="kategori.php">Kategori</a></li>
        <li><a href="produk.php">Produk</a></li>
        <li><a href="../service/logout.php">Logout</a></li>
    </ul>
</div>

<div class="content">
    <h2>Halaman Transaksi</h2>

    

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Tanggal</th>
                <th>Admin</th>
                <th>Produk</th>
                <th>Detail</th>
                <th>Member</th>
                <th>Total Harga</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $q = mysqli_query($conn, "
                SELECT t.*, m.nama_member, p.nama_produk 
                FROM transaksi t
                LEFT JOIN member m ON t.fid_member = m.id_member
                LEFT JOIN produk p ON t.fid_produk = p.id_produk
                ORDER BY t.id_transaksi DESC
            ");
            while ($row = mysqli_fetch_assoc($q)) {
                echo "<tr>
                        <td>{$row['id_transaksi']}</td>
                        <td>{$row['tgl_pembelian']}</td>
                        <td>{$row['fid_admin']}</td>
                        <td>" . ($row['nama_produk'] ?? '-') . "</td>
                        <td>{$row['detail']}</td>
                        <td>" . ($row['nama_member'] ?? '-') . "</td>
                        <td>Rp" . number_format($row['total_harga'], 0, ',', '.') . "</td>
                    </tr>";
            }
            ?>
        </tbody>
    </table>
</div>
</body>
</html>
