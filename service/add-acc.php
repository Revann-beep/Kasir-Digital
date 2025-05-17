<?php
include '../service/conection.php'; // pastikan koneksi berhasil

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Amankan password

    // Inisialisasi variabel gambar
    $gambar = NULL;

    // Upload file jika ada
    if (!empty($_FILES['gambar']['name'])) {
        $target_dir = "../assets/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true); // buat folder jika belum ada
        }
    
        $ext = strtolower(pathinfo($_FILES["gambar"]["name"], PATHINFO_EXTENSION)); // Pastikan ekstensi dalam huruf kecil
        $nama_file = 'admin_' . date('Ymd_His') . '.' . $ext;
        $target_file = $target_dir . $nama_file;

        // Validasi ekstensi file
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($ext, $allowed_extensions)) {
            die("Ekstensi file tidak diizinkan. Hanya file dengan ekstensi: " . implode(', ', $allowed_extensions) . " yang diperbolehkan.");
        }
        if (move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file)) {
            $gambar = $nama_file;
        }
    }

    // Simpan ke database
    $sql = "INSERT INTO admin (email, username, password, gambar) VALUES ('$email', '$username', '$password', " . ($gambar ? "'$gambar'" : "NULL") . ")";
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #f0f4f8, #e0f7fa);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .form-container {
            background: #ffffff;
            padding: 35px 30px;
            border-radius: 16px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-container h2 {
            margin-bottom: 25px;
            color: #2c3e50;
            text-align: center;
            font-weight: 600;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #34495e;
            font-weight: 500;
        }

        .input-group {
            position: relative;
            margin-bottom: 20px;
        }

        .input-group input {
            width: 100%;
            padding: 10px 12px 10px 40px;
            border: 1px solid #ccc;
            border-radius: 8px;
            transition: border-color 0.3s;
        }

        .input-group input:focus {
            border-color: #3498db;
            outline: none;
        }

        .input-group i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #888;
        }

        .btn {
            width: 100%;
            background-color: #3498db;
            color: white;
            border: none;
            padding: 12px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .btn:hover {
            background-color: #2980b9;
        }

        @media (max-width: 420px) {
            .form-container {
                padding: 25px 20px;
            }
        }
    </style>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>

<div class="form-container">
    <h2>➕ Tambah Admin</h2>
    <form action="add-acc.php" method="POST" enctype="multipart/form-data">
        <div class="input-group">
            <label>Email:</label>
            <i class="fas fa-envelope"></i>
            <input type="email" name="email" required>
        </div>

        <div class="input-group">
            <label>Username:</label>
            <i class="fas fa-user"></i>
            <input type="text" name="username" required>
        </div>

        <div class="input-group">
            <label>Foto (Opsional):</label>
            <i class="fas fa-image"></i>
            <input type="file" name="gambar" accept="image/*" style="padding-left: 40px;">
        </div>

        <div class="input-group">
            <label>Password:</label>
            <i class="fas fa-lock"></i>
            <input type="password" name="password" required>
        </div>

        <button type="submit" class="btn">Tambah Admin</button>
    </form>
</div>

</body>
</html>
