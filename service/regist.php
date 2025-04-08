<?php
session_start();
include 'conection.php'; // Koneksi database

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);

    if ($password !== $confirm_password) {
        echo "<script>alert('Password dan Konfirmasi Password tidak cocok!'); window.location.href='signup.php';</script>";
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Upload file
    $target_dir = "uploads/"; // Folder tempat menyimpan gambar
    $profile_picture = basename($_FILES["profile_picture"]["name"]);
    $target_file = $target_dir . time() . "_" . $profile_picture;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Cek apakah file adalah gambar
    $check = getimagesize($_FILES["profile_picture"]["tmp_name"]);
    if ($check === false) {
        echo "<script>alert('File bukan gambar!'); window.location.href='signup.php';</script>";
        exit();
    }

    // Validasi ukuran file (maksimal 2MB)
    if ($_FILES["profile_picture"]["size"] > 2000000) {
        echo "<script>alert('Ukuran gambar terlalu besar! Maksimal 2MB.'); window.location.href='signup.php';</script>";
        exit();
    }

    // Format gambar yang diperbolehkan
    $allowed_types = ["jpg", "jpeg", "png", "gif"];
    if (!in_array($imageFileType, $allowed_types)) {
        echo "<script>alert('Format gambar tidak didukung! Hanya JPG, JPEG, PNG, dan GIF.'); window.location.href='signup.php';</script>";
        exit();
    }

    // Pindahkan file ke folder uploads
    if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
        // Simpan data ke database
        $query = "INSERT INTO admin (email, username, password, profile_picture) 
                  VALUES ('$email', '$username', '$hashed_password', '$target_file')";
        if (mysqli_query($conn, $query)) {
            echo "<script>alert('Registrasi berhasil! Silakan login.'); window.location.href='login.html';</script>";
        } else {
            echo "<script>alert('Registrasi gagal! Silakan coba lagi.'); window.location.href='signup.php';</script>";
        }
    } else {
        echo "<script>alert('Gagal mengunggah gambar!'); window.location.href='signup.php';</script>";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up Page</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f4f4f4;
        }
        .container {
            display: flex;
            width: 700px;
            height: 450px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }
        .left {
            width: 50%;
            background: #ff6666;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .left img {
            width: 80%;
        }
        .right {
            width: 50%;
            background: #b0d9b1;
            padding: 40px;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .input-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }
        input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .links {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
        }
        .links a {
            text-decoration: none;
            color: #007BFF;
        }
        .register-btn {
            width: 100%;
            background: red;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
        }
        .register-btn:hover {
            background: darkred;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="left">
            <img src="../assets/logo toko.webp" alt="Register Image">
        </div>
        <div class="right">
            <h2>Sign UP</h2>
            <form action="index.php">
                <div class="input-group">
                    <label>Email:</label>
                    <input type="email" required>
                </div>
                <div class="input-group">
                    <label>Username:</label>
                    <input type="text" required>
                </div>
                <div class="input-group">
                    <label>Password:</label>
                    <input type="password" required>
                </div>
                <div class="input-group">
                    <label>Confirm Password:</label>
                    <input type="password" required>
                </div>
                <div class="links">
                    <span>Have already an account?</span>
                    <a href="index.php">Sign in here</a>
                </div>
                <button type="submit" class="register-btn">Register</button>
            </form>
        </div>
    </div>
</body>
</html>
