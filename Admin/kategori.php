<?php
$conn = new mysqli("localhost", "root", "", "kasir");

// Tambah / Edit Kategori
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = $_POST['nama_kategori'];
    if (isset($_POST['id_kategori'])) {
        // Edit
        $id = $_POST['id_kategori'];
        $conn->query("UPDATE kategori SET nama_kategori='$nama' WHERE id_kategori=$id");
    } else {
        // Tambah
        $conn->query("INSERT INTO kategori (nama_kategori) VALUES ('$nama')");
    }
    header("Location: kategori.php");
    exit();
}

// Hapus
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM kategori WHERE id_kategori=$id");
    header("Location: kategori.php");
    exit();
}

// Ambil data untuk form edit
$edit = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $res = $conn->query("SELECT * FROM kategori WHERE id_kategori=$id");
    $edit = $res->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kategori</title>
    <style>
        /* CSS sama seperti sebelumnya (dipendekkan) */
        body { display: flex; font-family: Arial; margin: 0; }
        .sidebar { width: 250px; background: #b8860b; padding: 20px; height: 100vh; }
        .sidebar ul { list-style: none; padding: 0; }
        .sidebar li { padding: 10px; }
        .sidebar li:hover { background: #A88A02; cursor: pointer; }
        .content { flex: 1; padding: 20px; }
        .table-container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: center; }
        th { background: #C4A103; }
        .btn { padding: 6px 10px; border: none; background: gray; color: white; border-radius: 4px; cursor: pointer; }
        .btn:hover { background: black; }
        form { margin-top: 20px; }
        input[type="text"] { padding: 8px; width: 300px; }
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
    <div class="table-container">
        <h3><?= $edit ? "Edit Kategori" : "Tambah Kategori" ?></h3>
        <form method="post">
            <input type="text" name="nama_kategori" placeholder="Nama Kategori" value="<?= $edit['nama_kategori'] ?? '' ?>" required>
            <?php if ($edit): ?>
                <input type="hidden" name="id_kategori" value="<?= $edit['id_kategori'] ?>">
            <?php endif; ?>
            <button type="submit" class="btn"><?= $edit ? "Update" : "+ Tambah" ?></button>
        </form>

        <h3 style="margin-top:30px;">Tabel Kategori</h3>
        <table>
            <tr>
                <th>ID Kategori</th>
                <th>Nama Kategori</th>
                <th>Tanggal Input</th>
                <th>Aksi</th>
            </tr>
            <?php
            $result = $conn->query("SELECT * FROM kategori ORDER BY id_kategori DESC");
            while ($row = $result->fetch_assoc()):
            ?>
            <tr>
                <td><?= $row['id_kategori'] ?></td>
                <td><?= $row['nama_kategori'] ?></td>
                <td><?= date('d-m-Y', strtotime($row['tgl_input'])) ?></td>
                <td>
                    <a href="kategori.php?edit=<?= $row['id_kategori'] ?>" class="btn">✏️</a>
                    <a href="kategori.php?delete=<?= $row['id_kategori'] ?>" class="btn" onclick="return confirm('Hapus kategori ini?')">🗑️</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>
</body>
</html>
