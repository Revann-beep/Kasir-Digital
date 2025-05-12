<?php
include '../service/conection.php'; // pastikan koneksi berhasil

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Amankan password

    // Upload file jika ada
    $gambar = null;
    if (!empty($_FILES['gambar']['name'])) {
        $target_dir = "../uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true); // buat folder jika belum ada
        }
        $nama_file = time() . '_' . basename($_FILES["gambar"]["name"]);
        $target_file = $target_dir . $nama_file;
        if (move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file)) {
            $gambar = $nama_file;
        }
    }

    // Simpan ke database
    $sql = "INSERT INTO admin (email, username, password, gambar) VALUES ('$email', '$username', '$password', '$gambar')";
    if (mysqli_query($conn, $sql)) {
        header("Location: ../admin/admin.php");
        exit;
    } else {
        echo "Gagal menambahkan admin: " . mysqli_error($conn);
    }
}
?>



<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Admin</title>
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #dfe9f3 0%, #ffffff 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .form-container {
            background: #ffffff;
            padding: 30px 25px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 360px;
        }

        .form-container h2 {
            margin-bottom: 25px;
            color: #333;
            text-align: center;
        }

        label {
            display: block;
            margin-bottom: 6px;
            color: #555;
            font-weight: 600;
        }

        input[type="email"],
        input[type="text"],
        input[type="password"],
        input[type="file"] {
            width: 100%;
            padding: 10px 12px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
            transition: border-color 0.3s ease;
        }

        input:focus {
            border-color: #4CAF50;
            outline: none;
        }

        input[type="file"] {
            padding: 5px;
        }

        .btn {
            width: 100%;
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 12px;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .btn:hover {
            background-color: #45a049;
        }

        @media (max-width: 400px) {
            .form-container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Tambah Admin</h2>
    <form action="add-acc.php" method="POST" enctype="multipart/form-data">
        <label>Email:</label>
        <input type="email" name="email" required>

        <label>Username:</label>
        <input type="text" name="username" required>

        <label>Foto (Opsional):</label>
        <input type="file" name="gambar" accept="image/*">

        <label>Password:</label>
        <input type="password" name="password" required>

        <button type="submit" class="btn">➕ Tambah Admin</button>
    </form>
</div>

</body>
</html>
