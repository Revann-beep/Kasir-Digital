<?php include '../service/conection.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>CRUD Member - TimelessWatch.co</title>
    <style>
       /* Reset default */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, sans-serif;
    background-color: #f4f7f9;
    color: #333;
    height: 100vh;
    display: flex;
    overflow: hidden;
}

.sidebar {
    width: 200px;
    background: #b8860b;
    padding: 20px;
    color: white;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

.sidebar h2 {
    margin-bottom: 20px;
    font-weight: 700;
    font-size: 20px;
}

.sidebar ul {
    list-style: none;
    padding: 0;
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
    transition: background-color 0.3s ease;
    padding: 8px 12px;
    border-radius: 6px;
}

.sidebar ul li a:hover {
    background-color: #9a6f02;
}

.sidebar ul li a.active {
    background-color: #9a6f02;
}

.main-content {
    flex-grow: 1;
    padding: 20px 30px;
    overflow-y: auto;
    background-color: #fff;
    box-shadow: inset 0 0 15px #ddd;
    border-radius: 8px;
    margin: 15px;
}

h2, h3 {
    color: #2c3e50;
    margin-bottom: 10px;
}

/* Table Styling */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    background: #fff;
    box-shadow: 0 0 8px rgba(0,0,0,0.05);
    border-radius: 8px;
    overflow: hidden;
}

th, td {
    padding: 12px 16px;
    border-bottom: 1px solid #eaeaea;
    text-align: left;
    font-size: 14px;
}

th {
    background: #3498db;
    color: white;
    text-transform: uppercase;
}

tr:hover {
    background-color: #f1f1f1;
}

/* Button Styles */
.btn {
    padding: 8px 14px;
    border: none;
    border-radius: 5px;
    font-size: 14px;
    text-decoration: none;
    display: inline-block;
    text-align: center;
    transition: background 0.3s ease;
    cursor: pointer;
    user-select: none;
}

.btn-edit {
    background-color: #f39c12;
    color: white;
}

.btn-edit:hover {
    background-color: #e67e22;
}

.btn-delete {
    background-color: #e74c3c;
    color: white;
}

.btn-delete:hover {
    background-color: #c0392b;
}

.btn-submit {
    background-color: #27ae60;
    color: white;
    width: 100%;
}

.btn-submit:hover {
    background-color: #1e8449;
}

/* Form Styles */
.form-container {
    background: white;
    padding: 20px;
    border-radius: 10px;
    margin-top: 30px;
    width: 100%;
    max-height: 300px;
    overflow-y: auto;
    box-shadow: 0 0 10px rgba(0,0,0,0.06);
}

input[type="text"],
input[type="number"],
select {
    width: 100%;
    padding: 10px 12px;
    margin: 8px 0 16px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 14px;
}

label {
    font-weight: bold;
    display: block;
}

/* Header bar with back button */
.header-bar {
    display: flex;
    justify-content: flex-start;
    align-items: center;
}

.btn-back {
    padding: 8px 14px;
    background-color: #555;
    color: white;
    border: none;
    border-radius: 4px;
    transition: background 0.3s;
    text-decoration: none;
    cursor: pointer;
    user-select: none;
}

.btn-back:hover {
    background-color: #444;
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
        <li><a href="member.php" class="active">👥 Member</a></li>
        <li><a href="../service/logout.php">↩️ Log out</a></li>
    </ul>
</div>

<div class="main-content">
    <div class="header-bar">
        <a href="dashboard.php" class="btn-back">← Kembali</a>
    </div>

    <h2>Data Member</h2>

    <table>
        <tr>
            <th>ID</th>
            <th>Nama Member</th>
            <th>No Telp</th>
            <th>Poin</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
        <?php
        $data = mysqli_query($conn, "SELECT * FROM member");
        while ($row = mysqli_fetch_assoc($data)) {
            echo "<tr>
                <td>{$row['id_member']}</td>
                <td>".htmlspecialchars($row['nama_member'])."</td>
                <td>".htmlspecialchars($row['no_telp'])."</td>
                <td>{$row['poin']}</td>
                <td>{$row['status']}</td>
                <td>
                    <a href='?edit={$row['id_member']}' class='btn btn-edit'>Edit</a>
                    <a href='?hapus={$row['id_member']}' onclick='return confirm(\"Yakin hapus?\")' class='btn btn-delete'>Hapus</a>
                </td>
            </tr>";
        }
        ?>
    </table>

    <?php
    // Inisialisasi variabel form
    $nama = "";
    $telp = "";
    $poin = 0;
    $status = "aktif";
    $edit_id = 0;

    if (isset($_GET['edit'])) {
        $edit_id = intval($_GET['edit']);
        $edit_query = mysqli_query($conn, "SELECT * FROM member WHERE id_member = $edit_id");
        if ($edit_query && mysqli_num_rows($edit_query) > 0) {
            $row = mysqli_fetch_assoc($edit_query);
            $nama = $row['nama_member'];
            $telp = $row['no_telp'];
            $poin = $row['poin'];
            $status = $row['status'];
        }
    }
    ?>

    <div class="form-container">
        <h3><?= $edit_id ? "Edit Member" : "Tambah Member"; ?></h3>
        <form method="POST" action="">
            <input type="hidden" name="id_member" value="<?= $edit_id; ?>">
            
            <label>Nama Member</label>
            <input type="text" name="nama_member" required value="<?= htmlspecialchars($nama); ?>">

            <label>No Telp</label>
            <input type="text" name="no_telp" required value="<?= htmlspecialchars($telp); ?>">

            <label>Poin</label>
            <input type="number" name="poin" min="0" value="<?= htmlspecialchars($poin); ?>">

            <label>Status</label>
            <select name="status">
                <option value="aktif" <?= $status == 'aktif' ? "selected" : ""; ?>>Aktif</option>
                <option value="tidak aktif" <?= $status == 'tidak aktif' ? "selected" : ""; ?>>Tidak Aktif</option>
            </select>

            <button type="submit" name="simpan" class="btn btn-submit">Simpan</button>
        </form>
    </div>

    <?php
    // Proses simpan (insert/update)
    if (isset($_POST['simpan'])) {
        $id_member = intval($_POST['id_member']);
        $nama_member = mysqli_real_escape_string($conn, $_POST['nama_member']);
        $no_telp = mysqli_real_escape_string($conn, $_POST['no_telp']);
        $poin = intval($_POST['poin']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);

        if ($id_member > 0) {
            // Update
            $sql_update = "UPDATE member SET 
                nama_member = '$nama_member', 
                no_telp = '$no_telp', 
                poin = $poin, 
                status = '$status' 
                WHERE id_member = $id_member";
            mysqli_query($conn, $sql_update);
        } else {
            // Insert
            $sql_insert = "INSERT INTO member (nama_member, no_telp, poin, status) 
                VALUES ('$nama_member', '$no_telp', $poin, '$status')";
            mysqli_query($conn, $sql_insert);
        }

        echo "<script>window.location='member.php';</script>";
        exit;
    }

    // Proses hapus
    if (isset($_GET['hapus'])) {
        $hapus_id = intval($_GET['hapus']);
        if ($hapus_id > 0) {
            mysqli_query($conn, "DELETE FROM member WHERE id_member = $hapus_id");
            echo "<script>window.location='member.php';</script>";
            exit;
        }
    }
    ?>
</div>

</body>
</html>
