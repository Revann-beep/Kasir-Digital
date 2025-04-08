<?php
include 'conection.php'; // Koneksi ke database

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

    // Cek apakah user mengupload gambar baru
    if ($_FILES['gambar']['name']) {
        $target_dir = "assets/"; // Folder penyimpanan gambar
        $target_file = $target_dir . basename($_FILES["gambar"]["name"]);
        move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file);
        $gambar = $target_file;
    } else {
        $gambar = $admin['gambar']; // Pakai gambar lama
    }

    // Update data admin di database
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Admin</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f4f4f4;
            font-family: Arial, sans-serif;
        }
        .form-container {
            background: #9ec3af;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
            text-align: center;
        }
        h2 {
            text-align: center;
            font-weight: bold;
        }
        label {
            font-weight: bold;
            display: block;
            margin-top: 10px;
            text-align: left;
        }
        input {
            width: 90%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background: blue;
            color: white;
        }
        input[type="file"] {
            background: none;
            color: black;
        }
        .btn {
            background: red;
            color: white;
            padding: 10px;
            border: none;
            cursor: pointer;
            width: 100%;
            margin-top: 15px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 5px;
        }
        .btn:hover {
            background: darkred;
        }
        img {
            width: 100px;
            margin-top: 10px;
            border-radius: 5px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Update Admin</h2>
    <form action="" method="POST" enctype="multipart/form-data">
        <label>Email:</label>
        <input type="email" name="email" value="<?= htmlspecialchars($admin['email']) ?>" required>

        <label>Username:</label>
        <input type="text" name="username" value="<?= htmlspecialchars($admin['username']) ?>" required>

        <label>Image:</label>
        <input type="file" name="gambar">
        
        <button type="submit" class="btn">Update Admin</button>
    </form>
</div>

</body>
</html>
