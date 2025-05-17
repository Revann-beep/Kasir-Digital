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
    <meta charset="UTF-8" />
    <title>Produk</title>
    <style>
        * {
            margin: 0; padding: 0; box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        body {
            display: flex;
            min-height: 100vh;
            background: #f4f4f4;
        }
        .sidebar {
            width: 250px;
            background: #b8860b;
            color: white;
            padding: 20px;
            height: 100vh;
        }
        .sidebar h2 {
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .sidebar ul {
            list-style: none;
            padding-left: 0;
        }
        .sidebar ul li {
            margin-bottom: 15px;
        }
        .sidebar ul li a {
            color: white;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            display: block;
            padding: 8px 12px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        .sidebar ul li a:hover {
            background: rgba(255, 255, 255, 0.2);
        }
        .content {
            flex-grow: 1;
            padding: 20px 30px;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            gap: 10px;
            align-items: center;
        }
        .add-btn {
            background: #000;
            color: #fff;
            padding: 10px 16px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            transition: background-color 0.3s ease;
            display: inline-block;
        }
        .add-btn:hover {
            background: #444;
        }
        .add-btn.green {
            background: #2ecc71;
        }
        .add-btn.green:hover {
            background: #27ae60;
        }
        form.search-form {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            flex-grow: 1;
        }
        form.search-form input[type="text"],
        form.search-form select {
            padding: 8px 12px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 15px;
            flex: 1;
            min-width: 200px;
            transition: border-color 0.3s ease;
        }
        form.search-form input[type="text"]:focus,
        form.search-form select:focus {
            border-color: #b8860b;
            outline: none;
        }
        form.search-form button {
            padding: 9px 16px;
            background: #b8860b;
            border: none;
            color: white;
            font-weight: 600;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        form.search-form button:hover {
            background: #a57608;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 6px rgb(0 0 0 / 0.1);
        }
        th, td {
            padding: 12px 10px;
            border-bottom: 1px solid #ddd;
            text-align: center;
            vertical-align: middle;
            font-size: 14px;
        }
        th {
            background: #b8860b;
            color: white;
            font-weight: 700;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        td img {
            max-width: 60px;
            height: auto;
            border-radius: 4px;
        }
        td a {
            text-decoration: none;
            margin: 0 5px;
            font-size: 18px;
        }
        td a:hover {
            opacity: 0.7;
        }

        .pagination {
            margin-top: 25px;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .pagination a {
            padding: 8px 14px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: background-color 0.3s ease;
            color: #333;
            background: #eee;
            border: 1px solid transparent;
        }
        .pagination a.active-page {
            background: #b8860b;
            color: white;
            border-color: #a57608;
        }
        .pagination a.inactive-page:hover {
            background: #d6b12f;
            color: white;
            border-color: #a57608;
        }

        /* Modal Styles */
        #barcodeModal {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.6);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        #barcodeModal > div {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            max-width: 320px;
            position: relative;
            box-shadow: 0 0 10px rgba(0,0,0,0.3);
        }
        #closeModal {
            position: absolute;
            top: 10px; right: 15px;
            cursor: pointer;
            font-size: 24px;
            font-weight: bold;
            user-select: none;
        }
        #modalBarcodeImg {
            max-width: 100%;
            height: auto;
            margin-bottom: 15px;
        }
        #modalBarcodeText {
            font-weight: bold;
            font-size: 18px;
            user-select: text;
            word-break: break-all;
        }

        @media (max-width: 768px) {
            .top-bar {
                flex-direction: column;
                align-items: stretch;
            }
            form.search-form {
                flex-direction: column;
                gap: 10px;
            }
            form.search-form input[type="text"],
            form.search-form select {
                width: 100%;
            }
            .add-btn {
                width: 100%;
                text-align: center;
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
        <li><a href="produk.php" style="background: rgba(255, 255, 255, 0.2); border-radius: 5px;">📦 Produk</a></li>
        <li><a href="../service/logout.php">↩️ Log out</a></li>
    </ul>
</div>
<div class="content">
    <a href="../Scanner/Scan.php" class="add-btn green" style="margin-bottom: 20px;">🛒 Transaksi Sekarang</a>

    <div class="top-bar">
        <form method="GET" class="search-form">
            <input type="text" name="search" placeholder="Cari produk atau barcode..." value="<?= htmlspecialchars($keyword) ?>">
            <select name="kategori">
                <option value="">Semua Kategori</option>
                <?php
                mysqli_data_seek($kategoriResult, 0);
                while ($kat = mysqli_fetch_assoc($kategoriResult)) { ?>
                    <option value="<?= $kat['id_kategori'] ?>" <?= $filter_kategori == $kat['id_kategori'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($kat['nama_kategori']) ?>
                    </option>
                <?php } ?>
            </select>
            <button type="submit">🔍</button>
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
                <td><img src="../assets/<?= htmlspecialchars($row['gambar']) ?>" alt="<?= htmlspecialchars($row['nama_produk']) ?>"></td>
                <td><?= htmlspecialchars($row['nama_produk']) ?></td>
                <td><?= $row['stok'] ?></td>
                <td><?= number_format($row['modal'], 0, ',', '.') ?></td>
                <td><?= number_format($row['harga_jual'], 0, ',', '.') ?></td>
                <td><?= number_format($row['keuntungan'], 0, ',', '.') ?></td>
                <td>
                    <img 
                      src="../service/barcode.php?text=<?= urlencode($row['barcode']) ?>&size=60&orientation=horizontal&code=Code128" 
                      alt="barcode" 
                      style="width: 150px; height: 50px; cursor:pointer;" 
                      class="barcode-img"
                      data-barcode="<?= htmlspecialchars($row['barcode']) ?>"
                      data-imgsrc="../service/barcode.php?text=<?= urlencode($row['barcode']) ?>&size=250&orientation=horizontal&code=Code128"
                    >
                </td>
                <td><?= htmlspecialchars($row['nama_kategori']) ?></td>
                <td><?= htmlspecialchars($row['deskripsi']) ?></td>
                <td>
                    <a href="../service/edit-produk.php?id=<?= $row['id_produk'] ?>" title="Edit" style="color: blue;">✏️</a>
                    <a href="../service/hapus-produk.php?id=<?= $row['id_produk'] ?>" title="Delete" onclick="return confirm('Yakin ingin menghapus produk ini?')" style="color: red;">🗑️</a>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>

    <div class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++) { ?>
            <a href="?page=<?= $i ?>&search=<?= urlencode($keyword) ?>&kategori=<?= urlencode($filter_kategori) ?>"
               class="<?= $i === $page ? 'active-page' : 'inactive-page' ?>">
                <?= $i ?>
            </a>
        <?php } ?>
    </div>
</div>

<!-- Modal for barcode -->
<div id="barcodeModal">
    <div>
        <span id="closeModal">&times;</span>
        <img id="modalBarcodeImg" src="" alt="Barcode">
        <div id="modalBarcodeText"></div>
    </div>
</div>

<script>
    document.querySelectorAll('.barcode-img').forEach(img => {
        img.addEventListener('click', () => {
            const modal = document.getElementById('barcodeModal');
            const modalImg = document.getElementById('modalBarcodeImg');
            const modalText = document.getElementById('modalBarcodeText');
            const barcode = img.getAttribute('data-barcode');
            const imgSrc = img.getAttribute('data-imgsrc');

            modalImg.src = imgSrc;
            modalText.textContent = barcode;
            modal.style.display = 'flex';
        });
    });

    document.getElementById('closeModal').addEventListener('click', () => {
        document.getElementById('barcodeModal').style.display = 'none';
    });

    // Close modal if clicked outside modal content
    document.getElementById('barcodeModal').addEventListener('click', (e) => {
        if (e.target.id === 'barcodeModal') {
            e.target.style.display = 'none';
        }
    });
</script>
</body>
</html>
