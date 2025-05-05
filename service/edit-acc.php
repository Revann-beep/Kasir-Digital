<?php
include 'conection.php';

if (!isset($_GET['id'])) {
    header("Location: admin.php");
    exit();
}

$id = $_GET['id'];
$result = $conn->query("SELECT * FROM admin WHERE id = $id");

if ($result->num_rows == 0) {
    echo "Admin tidak ditemukan!";
    exit();
}

$admin = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $username = $_POST['username'];

    // Cek apakah upload gambar baru
    if ($_FILES['gambar']['name']) {
        $namaFile = time() . '_' . $_FILES['gambar']['name'];
        $target_dir = "../assets/";
        move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_dir . $namaFile);
        $gambar = $namaFile;
    } else {
        $gambar = $admin['gambar']; // Tetap pakai gambar lama
    }

    $sql = "UPDATE admin SET email='$email', username='$username', gambar='$gambar' WHERE id=$id";

    if ($conn->query($sql) === TRUE) {
        header("Location: ../admin/admin.php");
        exit();
    } else {
        echo "Error updating record: " . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Admin</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 200px;
            background: #b8860b;
            padding: 20px;
            color: white;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
        }
        .sidebar ul li {
            padding: 10px;
        }
        .content {
            flex-grow: 1;
            padding: 20px;
            background: #f4f4f4;
        }
        .form-container {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            width: 400px;
            margin: auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
        }
        label {
            font-weight: bold;
            display: block;
            margin-top: 10px;
        }
        input {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        input[type="file"] {
            padding: 0;
            background: none;
        }
        .btn {
            background: #b8860b;
            color: white;
            padding: 10px;
            margin-top: 15px;
            border: none;
            width: 100%;
            font-size: 16px;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn:hover {
            background: #a67300;
        }
        img {
            display: block;
            margin: 10px auto;
            width: 100px;
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

<main class="content">
    <div class="form-container">
        <h2>Edit Admin</h2>
        <form action="" method="POST" enctype="multipart/form-data">
            <label>Email:</label>
            <input type="email" name="email" value="<?= htmlspecialchars($admin['email']) ?>" required>

            <label>Username:</label>
            <input type="text" name="username" value="<?= htmlspecialchars($admin['username']) ?>" required>

            <label>Gambar Baru (opsional):</label>
            <input type="file" name="gambar">

            <label>Gambar Sekarang:</label>
            <img src="../assets/<?= $admin['gambar'] ?>" alt="User">

            <button type="submit" class="btn">Simpan Perubahan</button>
        </form>
    </div>
</main>

</body>
</html>
