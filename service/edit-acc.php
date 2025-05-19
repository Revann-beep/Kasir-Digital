<?php
include 'conection.php';
session_start();

if (!isset($_GET['id'])) {
    header("Location: admin.php");
    exit();
}

$id = (int) $_GET['id'];
$result = $conn->query("SELECT * FROM admin WHERE id = $id");

if ($result->num_rows == 0) {
    echo "Admin tidak ditemukan!";
    exit();
}

$admin = $result->fetch_assoc();
$errors = [];

$current_user_id = $_SESSION['admin_id'] ?? null; // ID admin yang sedang login
$is_self = ($id == $current_user_id);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = $is_self ? trim($_POST['email']) : $admin['email']; // hanya edit sendiri yang bisa ubah email

    // Validasi duplikat email jika ubah email
    if ($is_self && $email !== $admin['email']) {
        $email_escaped = mysqli_real_escape_string($conn, $email);
        $check = $conn->query("SELECT id FROM admin WHERE email = '$email_escaped' AND id != $id");
        if ($check->num_rows > 0) {
            $errors[] = "Email sudah digunakan admin lain.";
        }
    }

    // Validasi gambar jika diunggah
    if (!empty($_FILES['gambar']['name'])) {
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
        $file_size = $_FILES['gambar']['size'];

        if (!in_array($ext, $allowed_ext)) {
            $errors[] = "Ekstensi gambar tidak valid.";
        } elseif ($file_size > 2 * 1024 * 1024) {
            $errors[] = "Ukuran gambar maksimal 2MB.";
        } else {
            $namaFile = 'admin_' . time() . '.' . $ext;
            $target_dir = "../assets/";
            move_uploaded_file($_FILES['gambar']['tmp_name'], $target_dir . $namaFile);
        }
    } else {
        $namaFile = $admin['gambar']; // tetap pakai gambar lama
    }

    if (empty($errors)) {
        $email_escaped = mysqli_real_escape_string($conn, $email);
        $username_escaped = mysqli_real_escape_string($conn, $username);
        $gambar_escaped = mysqli_real_escape_string($conn, $namaFile);

        $sql = "UPDATE admin SET 
                    email = '$email_escaped',
                    username = '$username_escaped',
                    gambar = '$gambar_escaped'
                WHERE id = $id";

        if ($conn->query($sql) === TRUE) {
            header("Location: ../admin/admin.php");
            exit();
        } else {
            $errors[] = "Gagal memperbarui data: " . $conn->error;
        }
    }
}
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

        .error {
            background: #ffe6e6;
            color: #c0392b;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 15px;
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

    <?php if (!empty($errors)): ?>
        <div class="error">
            <ul>
                <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data">
        <?php if ($is_self): ?>
            <label>Email:</label>
            <input type="email" name="email" value="<?= htmlspecialchars($admin['email']) ?>" required>
        <?php else: ?>
            <label>Email:</label>
            <input type="email" value="<?= htmlspecialchars($admin['email']) ?>" disabled>
        <?php endif; ?>

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
