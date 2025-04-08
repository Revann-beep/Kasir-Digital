<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    cursor: pointer;
}

.logout {
    margin-top: 20px;
    font-weight: bold;
}

.content {
    flex-grow: 1;
    padding: 20px;
}

.top-bar {
    display: flex;
    justify-content: space-between;
    margin-bottom: 20px;
}

.add-btn {
    background: black;
    color: white;
    padding: 8px 12px;
    border: none;
    cursor: pointer;
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

img {
    width: 50px;
    height: auto;
}

button {
    border: none;
    cursor: pointer;
    margin: 0 5px;
}
.add-btn {
    background: black;
    color: white;
    padding: 8px 12px;
    text-decoration: none;
    border-radius: 4px;
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
        <div class="top-bar">
            <input type="text" placeholder="Search">
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
            <th>Expired</th>
            <th>Kategori</th> <!-- ganti jadi nama kategori -->
            <th>Deskripsi</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php
        include '../service/conection.php';
        $query = "SELECT produk.*, kategori.nama_kategori 
                  FROM produk 
                  JOIN kategori ON produk.fid_kategori = kategori.id_kategori";
        $result = mysqli_query($conn, $query);

        while ($row = mysqli_fetch_assoc($result)) {
        ?>
            <tr>
                <td><?= $row['id_produk'] ?></td>
                <td><img src="../assets/<?= $row['gambar'] ?>" alt="<?= $row['nama_produk'] ?>"></td>
                <td><?= $row['nama_produk'] ?></td>
                <td><?= $row['stok'] ?></td>
                <td><?= number_format($row['modal'], 0, ',', '.') ?></td>
                <td><?= number_format($row['harga_jual'], 0, ',', '.') ?></td>
                <td><?= number_format($row['keuntungan'], 0, ',', '.') ?></td>
                <td><?= $row['barcode'] ?></td>
                <td><?= $row['nama_kategori'] ?></td>
                <td><?= $row['deskripsi'] ?></td>
                <td>
                    <a href="edit-produk.php?id=<?= $row['id_produk'] ?>">✏️</a>
                    <a href="delete-produk.php?id=<?= $row['id_produk'] ?>" onclick="return confirm('Yakin mau hapus?')">🗑️</a>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>
    </div>
</body>
</html>