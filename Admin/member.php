<?php include '../service/conection.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>CRUD Member</title>
    <style>
        /* Reset default */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', sans-serif;
    background-color: #f4f7f9;
    color: #333;
    padding: 20px;
}

h2, h3 {
    margin-bottom: 10px;
    color: #2c3e50;
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
}

th {
    background: #3498db;
    color: white;
    text-transform: uppercase;
    font-size: 14px;
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
    max-width: 500px;
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
}

label {
    font-weight: bold;
    display: block;
}

.header-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.btn-back {
    padding: 8px 14px;
    background-color: #555;
    color: white;
    border: none;
    cursor: pointer;
    border-radius: 4px;
    transition: background 0.3s;
    text-decoration: none; /* Supaya gak kelihatan seperti link biru */
}



    </style>
</head>
<body>

<div class="header-bar">
<a href="dashboard.php" class="btn-back">← Kembali</a>

</div>
<h2 style="text-align: center; margin-top: -30px;">Data Member</h2>



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
