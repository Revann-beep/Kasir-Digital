<?php include '../service/conection.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>CRUD Member</title>
    <style>
        body {
            font-family: Arial;
            margin: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }
        th {
            background: #eee;
        }
        input, select {
            padding: 6px;
            width: 100%;
        }
        .form-container {
            margin-top: 20px;
            max-width: 400px;
        }
        .btn {
            padding: 8px 12px;
            margin-top: 10px;
            cursor: pointer;
        }
        .btn-edit {
            background: orange;
            color: white;
        }
        .btn-delete {
            background: red;
            color: white;
        }
        .btn-submit {
            background: green;
            color: white;
        }
    </style>
</head>
<body>

<h2>Data Member</h2>

<table>
    <tr>
        <th>ID</th>
        <th>Nama Member</th>
        <th>No Telp</th>
        <th>Point</th>
        <th>Status</th>
        <th>Aksi</th>
    </tr>
    <?php
    $data = mysqli_query($conn, "SELECT * FROM member");
    while ($row = mysqli_fetch_array($data)) {
        echo "<tr>
            <td>$row[id_member]</td>
            <td>$row[nama_member]</td>
            <td>$row[no_telp]</td>
            <td>$row[point]</td>
            <td>$row[status]</td>
            <td>
                <a href='?edit=$row[id_member]' class='btn btn-edit'>Edit</a> |
                <a href='?hapus=$row[id_member]' onclick='return confirm(\"Yakin hapus?\")' class='btn btn-delete'>Hapus</a>
            </td>
        </tr>";
    }
    ?>
</table>

<?php
// Form tambah/edit
$nama = ""; $telp = ""; $point = ""; $status = "aktif"; $edit_id = "";

if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $edit_data = mysqli_query($conn, "SELECT * FROM member WHERE id_member=$edit_id");
    $row = mysqli_fetch_array($edit_data);
    $nama = $row['nama_member'];
    $telp = $row['no_telp'];
    $point = $row['point'];
    $status = $row['status'];
}
?>

<div class="form-container">
    <h3><?php echo $edit_id ? "Edit Member" : "Tambah Member"; ?></h3>
    <form method="POST">
        <input type="hidden" name="id_member" value="<?php echo $edit_id; ?>">
        <label>Nama Member</label>
        <input type="text" name="nama_member" required value="<?php echo $nama; ?>">

        <label>No Telp</label>
        <input type="text" name="no_telp" required value="<?php echo $telp; ?>">

        <label>Point</label>
        <input type="number" name="point" value="<?php echo $point; ?>">

        <label>Status</label>
        <select name="status">
            <option value="aktif" <?php if($status == 'aktif') echo "selected"; ?>>Aktif</option>
            <option value="tidak aktif" <?php if($status == 'tidak aktif') echo "selected"; ?>>Tidak Aktif</option>
        </select>

        <button type="submit" name="simpan" class="btn btn-submit">Simpan</button>
    </form>
</div>

<?php
// Simpan (Insert/Update)
if (isset($_POST['simpan'])) {
    $id = $_POST['id_member'];
    $nama = $_POST['nama_member'];
    $telp = $_POST['no_telp'];
    $point = $_POST['point'];
    $status = $_POST['status'];

    if ($id) {
        // Update
        mysqli_query($conn, "UPDATE member SET 
            nama_member='$nama',
            no_telp='$telp',
            point='$point',
            status='$status'
            WHERE id_member=$id
        ");
    } else {
        // Insert
        mysqli_query($conn, "INSERT INTO member (nama_member, no_telp, point, status)
            VALUES ('$nama', '$telp', '$point', '$status')");
    }

    echo "<script>window.location='member.php';</script>";
}

// Hapus
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM member WHERE id_member=$id");
    echo "<script>window.location='member.php';</script>";
}
?>

</body>
</html>
