<?php
include '../service/conection.php'; // pastikan koneksi

$email = $username = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $gambar = NULL;

    // Escape input untuk keamanan query
    $email_escaped = mysqli_real_escape_string($conn, $email);
    $username_escaped = mysqli_real_escape_string($conn, $username);
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Cek email sudah ada
    $cekEmail = mysqli_query($conn, "SELECT id FROM admin WHERE email = '$email_escaped'");
    if (mysqli_num_rows($cekEmail) > 0) {
        $errors[] = "Email sudah digunakan admin lain.";
    }

    // Validasi gambar jika ada
    if (!empty($_FILES['gambar']['name'])) {
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        $target_dir = "../assets/";

        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
        $file_size = $_FILES['gambar']['size'];

        if (!in_array($ext, $allowed_ext)) {
            $errors[] = "Ekstensi gambar tidak valid. Harus JPG, JPEG, PNG, atau GIF.";
        } elseif ($file_size > 2 * 1024 * 1024) {
            $errors[] = "Ukuran gambar maksimal 2MB.";
        } else {
            $nama_file = 'admin_' . date('Ymd_His') . '.' . $ext;
            $target_file = $target_dir . $nama_file;
            if (move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file)) {
                $gambar = $nama_file;
            } else {
                $errors[] = "Gagal mengupload gambar.";
            }
        }
    }

    // Jika tidak ada error, simpan ke database
    if (empty($errors)) {
        $sql = "INSERT INTO admin (email, username, password, gambar, status) 
        VALUES ('$email_escaped', '$username_escaped', '$password_hash', " . ($gambar ? "'$gambar'" : "NULL") . ", 'tidak aktif')";

        if (mysqli_query($conn, $sql)) {
            header("Location: ../admin/admin.php");
            exit;
        } else {
            $errors[] = "Gagal menyimpan data: " . mysqli_error($conn);
        }
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
        h2 {
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
            margin-bottom: 20px;
        }
        .input-group input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            transition: border-color 0.3s;
        }
        .input-group input:focus {
            border-color: #3498db;
            outline: none;
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
        .error {
            background: #fce4e4;
            color: #c0392b;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        @media (max-width: 420px) {
            .form-container {
                padding: 25px 20px;
            }
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>➕ Tambah Admin</h2>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <ul>
                <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="add-acc.php" method="POST" enctype="multipart/form-data">
        <div class="input-group">
            <label>Email:</label>
            <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
        </div>

        <div class="input-group">
            <label>Username:</label>
            <input type="text" name="username" value="<?= htmlspecialchars($username) ?>" required>
        </div>

        <div class="input-group">
            <label>Foto (Opsional):</label>
            <input type="file" name="gambar" accept="image/png, image/jpeg, image/jpg, image/gif">
        </div>

        <div class="input-group">
            <label>Password:</label>
            <input type="password" name="password" required>
        </div>

        <button type="submit" class="btn">Tambah Admin</button>
    </form>
</div>

</body>
</html>
