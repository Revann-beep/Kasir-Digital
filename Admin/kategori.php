<?php
$conn = new mysqli("localhost", "root", "", "kasir");

// Tambah / Edit Kategori
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = trim($_POST['nama_kategori']);
    $nama = $conn->real_escape_string($nama);

    if (isset($_POST['id_kategori'])) {
        // Edit
        $id = intval($_POST['id_kategori']);
        $cek = $conn->query("SELECT COUNT(*) AS total FROM kategori WHERE nama_kategori = '$nama' AND id_kategori != $id");
        $data = $cek->fetch_assoc();
        if ($data['total'] > 0) {
            echo "<script>alert('Nama kategori sudah ada!'); window.location='kategori.php';</script>";
            exit();
        }

        $conn->query("UPDATE kategori SET nama_kategori='$nama' WHERE id_kategori=$id");
    } else {
        // Tambah
        $cek = $conn->query("SELECT COUNT(*) AS total FROM kategori WHERE nama_kategori = '$nama'");
        $data = $cek->fetch_assoc();
        if ($data['total'] > 0) {
            echo "<script>alert('Nama kategori sudah ada!'); window.location='kategori.php';</script>";
            exit();
        }

        $conn->query("INSERT INTO kategori (nama_kategori, tgl_input) VALUES ('$nama', NOW())");
    }

    header("Location: kategori.php");
    exit();
}

// Hapus (dengan pengecekan relasi ke produk)
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    // Cek apakah kategori masih dipakai di tabel produk
    $cek_produk = $conn->query("SELECT id_produk FROM produk WHERE fid_kategori = $id");

    if ($cek_produk->num_rows > 0) {
        echo "<script>alert('Kategori tidak bisa dihapus karena masih digunakan oleh produk!'); window.location='kategori.php';</script>";
        exit();
    }

    // Jika tidak dipakai, lanjut hapus
    $conn->query("DELETE FROM kategori WHERE id_kategori=$id");
    header("Location: kategori.php");
    exit();
}

// Ambil data untuk form edit
$edit = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $res = $conn->query("SELECT * FROM kategori WHERE id_kategori=$id");
    $edit = $res->fetch_assoc();
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Kategori</title>
    <style>
        body { display: flex; font-family: Arial, sans-serif; margin: 0; }
        .sidebar { width: 250px; background: #b8860b; padding: 20px; height: 100vh; color: white; }
        .sidebar ul { list-style: none; padding: 0; }
        .sidebar li { padding: 10px; }
        .sidebar li:hover { background: #A88A02; cursor: pointer; }
        .sidebar a { color: white; text-decoration: none; display: block; }
        .content { flex: 1; padding: 20px; background: #f8f8f8; }
        .table-container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: center; }
        th { background: #C4A103; color: white; }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 8px 16px;
            border: none;
            background-color: #b8860b;
            color: white;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: background-color 0.3s ease;
            user-select: none;
            text-decoration: none;
        }
        .btn:hover {
            background-color: #9a6f02;
        }
        .btn:active {
            background-color: #7c5700;
        }
        .btn-icon {
            font-size: 18px;
            line-height: 1;
        }

        td a.btn {
            padding: 6px 10px;
            font-size: 16px;
            min-width: 36px;
            justify-content: center;
        }
        td a.btn:hover {
            background-color: #8a5e00;
        }
        td a.btn + a.btn {
            margin-left: 8px;
        }

        input[type="text"] {
            padding: 8px;
            width: 300px;
            border-radius: 4px;
            border: 1px solid #ccc;
            font-size: 14px;
        }

        h3 {
            color: #5a4d00;
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
        <div class="table-container">
            <h3><?= $edit ? "Edit Kategori" : "Tambah Kategori" ?></h3>
            <form method="post">
                <input type="text" name="nama_kategori" placeholder="Nama Kategori" value="<?= htmlspecialchars($edit['nama_kategori'] ?? '') ?>" required>
                <?php if ($edit): ?>
                    <input type="hidden" name="id_kategori" value="<?= $edit['id_kategori'] ?>">
                <?php endif; ?>
                <button type="submit" class="btn">
                    <?= $edit ? '<span class="btn-icon">✏️</span> Update' : '<span class="btn-icon">＋</span> Tambah' ?>
                </button>
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
                    <td><?= htmlspecialchars($row['nama_kategori']) ?></td>
                    <td><?= date('d-m-Y', strtotime($row['tgl_input'])) ?></td>
                    <td>
                        <a href="kategori.php?edit=<?= $row['id_kategori'] ?>" class="btn" title="Edit">
                            <span class="btn-icon">✏️</span>
                        </a>
                        <a href="kategori.php?delete=<?= $row['id_kategori'] ?>" class="btn" title="Hapus" onclick="return confirm('Hapus kategori ini?')">
                            <span class="btn-icon">🗑️</span>
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>
</body>
</html>
