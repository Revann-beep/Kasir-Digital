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
        * {
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #e0f7fa, #ffffff);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .form-container {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: 600;
            color: #555;
        }

        input[type="email"],
        input[type="text"],
        input[type="file"] {
            width: 100%;
            padding: 10px 12px;
            margin-top: 6px;
            border: 1px solid #ccc;
            border-radius: 6px;
            transition: border-color 0.3s ease;
        }

        input[type="file"] {
            padding: 5px;
        }

        input:focus {
            border-color: #26a69a;
            outline: none;
        }

        .btn {
            margin-top: 25px;
            width: 100%;
            background-color: #26a69a;
            color: white;
            border: none;
            padding: 12px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .btn:hover {
            background-color: #1e8e81;
        }

        .preview-img {
            display: block;
            margin: 20px auto 10px;
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #ccc;
        }

        @media (max-width: 480px) {
            .form-container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Edit Admin</h2>
    <form action="" method="POST" enctype="multipart/form-data">
        <label>Email:</label>
        <input type="email" name="email" value="<?= htmlspecialchars($admin['email']) ?>" required>

        <label>Username:</label>
        <input type="text" name="username" value="<?= htmlspecialchars($admin['username']) ?>" required>

        <label>Gambar Baru (opsional):</label>
        <input type="file" name="gambar" accept="image/*">

        <label>Gambar Saat Ini:</label>
        <img src="../assets/<?= htmlspecialchars($admin['gambar']) ?>" alt="Foto Admin" class="preview-img">

        <button type="submit" class="btn">💾 Simpan Perubahan</button>
    </form>
</div>

</body>
</html>
