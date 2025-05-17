<?php
session_start();
require_once '../service/conection.php';

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password_input = $_POST['password']; // tidak perlu di-escape karena tidak disimpan ke DB langsung

    $query = "SELECT * FROM admin WHERE username='$username'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $hashed_password = $row['password'];

        if (password_verify($password_input, $hashed_password)) {
            // Simpan data ke session
            $_SESSION['admin_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['gambar'] = $row['gambar'];

            // Set semua admin jadi Nonaktif
            $conn->query("UPDATE admin SET status = 'Nonaktif'");

            // Set admin yang login jadi Aktif
            $adminId = $row['id'];
            $conn->query("UPDATE admin SET status = 'Aktif' WHERE id = $adminId");

            header("Location: ../admin/dashboard.php");
            exit;
        } else {
            echo "<script>alert('Password salah!'); window.location.href='index.php';</script>";
            exit;
        }
    } else {
        echo "<script>alert('Username tidak ditemukan!'); window.location.href='index.php';</script>";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
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
            height: 400px;
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
        .login-btn {
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
    </style>
</head>
<body>
    <div class="container">
        <div class="left">
            <img src="../assets/logo toko.webp" alt="Login Image">
        </div>
        <div class="right">
            <h2>Sign in</h2>
            <form method="POST" action="">
                <div class="input-group">
                    <label>Username:</label>
                    <input type="text" name="username" required>
                </div>
                <div class="input-group">
                    <label>Password:</label>
                    <input type="password" name="password" required>
                </div>
                <div class="links">
                    <span>Have already an account?</span>
                    <a href="../service/pass.php">Forgot password?</a>
                </div>
                <button type="submit" class="login-btn">Login</button>
            </form>
        </div>
    </div>
</body>
</html>
