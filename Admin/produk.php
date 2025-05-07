<?php
include '../service/conection.php';

// Ambil semua kategori untuk dropdown
$kategoriResult = mysqli_query($conn, "SELECT * FROM kategori");

// Konfigurasi pagination
$limit = 10;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Ambil parameter pencarian dan kategori
$keyword = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$filter_kategori = isset($_GET['kategori']) ? mysqli_real_escape_string($conn, $_GET['kategori']) : '';

// Query hitung total data
$countQuery = "SELECT COUNT(*) AS total FROM produk 
               JOIN kategori ON produk.fid_kategori = kategori.id_kategori
               WHERE 1";

if (!empty($keyword)) {
    $countQuery .= " AND (produk.nama_produk LIKE '%$keyword%' OR produk.barcode LIKE '%$keyword%')";
}
if (!empty($filter_kategori)) {
    $countQuery .= " AND produk.fid_kategori = '$filter_kategori'";
}

$countResult = mysqli_query($conn, $countQuery);
$totalData = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($totalData / $limit);

// Query produk
$query = "SELECT produk.*, kategori.nama_kategori 
          FROM produk 
          JOIN kategori ON produk.fid_kategori = kategori.id_kategori
          WHERE 1";

if (!empty($keyword)) {
    $query .= " AND (produk.nama_produk LIKE '%$keyword%' OR produk.barcode LIKE '%$keyword%')";
}
if (!empty($filter_kategori)) {
    $query .= " AND produk.fid_kategori = '$filter_kategori'";
}
$query .= " LIMIT $start, $limit";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Produk</title>
    <style>
        body {
            display: flex;
            font-family: Arial, sans-serif;
            margin: 0;
        }

        .sidebar {
            width: 200px;
            background: #b8860b;
            color: white;
            padding: 20px;
            height: 100vh;
        }

        .sidebar h2 {
            text-align: center;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar ul li {
            padding: 10px;
        }

        .sidebar ul li a {
            color: white;
            text-decoration: none;
        }

        .content {
            flex-grow: 1;
            padding: 20px;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            gap: 10px;
        }

        .add-btn {
            background: black;
            color: white;
            padding: 8px 12px;
            text-decoration: none;
            border-radius: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }

        th {
            background: #caa800;
            color: white;
        }

        .pagination a {
            margin-right: 5px;
            padding: 5px 10px;
            text-decoration: none;
            border-radius: 3px;
        }

        .active-page {
            background: #caa800;
            color: white;
        }

        .inactive-page {
            background: #eee;
            color: black;
        }

        form.search-form {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        form.search-form input,
        form.search-form select {
            padding: 6px;
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
        <li><a href="../service/index.php">↩️ Log out</a></li>
    </ul>
</div>
<div class="content">
    <a href="../Scanner/Scan.php" class="add-btn" style="background: green; margin-bottom: 15px; display: inline-block;">🛒 Transaksi Sekarang</a>

    <div class="top-bar">
        <form method="GET" class="search-form">
            <input type="text" name="search" placeholder="Cari produk atau barcode..." value="<?= htmlspecialchars($keyword) ?>">
            <select name="kategori">
                <option value="">Semua Kategori</option>
                <?php while ($kat = mysqli_fetch_assoc($kategoriResult)) { ?>
                    <option value="<?= $kat['id_kategori'] ?>" <?= $filter_kategori == $kat['id_kategori'] ? 'selected' : '' ?>>
                        <?= $kat['nama_kategori'] ?>
                    </option>
                <?php } ?>
            </select>
            <button type="submit" class="add-btn">🔍</button>
        </form>
        <a href="../service/add-produk.php" class="add-btn">+ Add Product</a>
    </div>

    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Image</th>
            <th>Name Product</th>
            <th>Qty</th>
            <th>Modal</th>
            <th>Harga</th>
            <th>Keuntungan</th>
            <th>Barcode</th>
            <th>Kategori</th>
            <th>Deskripsi</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
            <tr>
                <td><?= $row['id_produk'] ?></td>
                <td><img src="../assets/<?= $row['gambar'] ?>" alt="<?= $row['nama_produk'] ?>" style="width: 50px;"></td>
                <td><?= $row['nama_produk'] ?></td>
                <td><?= $row['stok'] ?></td>
                <td><?= number_format($row['modal'], 0, ',', '.') ?></td>
                <td><?= number_format($row['harga_jual'], 0, ',', '.') ?></td>
                <td><?= number_format($row['keuntungan'], 0, ',', '.') ?></td>
                <td>
                    <img src="../service/barcode.php?text=<?= $row['barcode'] ?>&size=60&orientation=horizontal&code=Code128" 
                         alt="barcode" style="width: 150px; height: 50px;">
                </td>
                <td><?= $row['nama_kategori'] ?></td>
                <td><?= $row['deskripsi'] ?></td>
                <td>
                    <a href="../service/edit-produk.php?id=<?= $row['id_produk'] ?>">✏️</a>
                    <a href="../service/delete-produk.php?id=<?= $row['id_produk'] ?>" onclick="return confirm('Yakin mau hapus?')">🗑️</a>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <div class="pagination" style="margin-top: 20px;">
        <?php if ($totalPages > 1): ?>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?= $i ?>&search=<?= urlencode($keyword) ?>&kategori=<?= urlencode($filter_kategori) ?>"
                   class="<?= $page == $i ? 'active-page' : 'inactive-page' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
