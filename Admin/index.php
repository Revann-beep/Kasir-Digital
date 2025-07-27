<?php
session_start();
require_once '../service/conection.php';

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password_input = $_POST['password'];

    $query = "SELECT * FROM admin WHERE username='$username'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $hashed_password = $row['password'];

        if (password_verify($password_input, $hashed_password)) {
            $_SESSION['admin_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['gambar'] = $row['gambar'];
            $_SESSION['welcome_message'] = "Selamat datang admin " . $row['username'];

            $conn->query("UPDATE admin SET status = 'Nonaktif'");
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
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    * {
      margin: 0; padding: 0; box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }

    body {
      background-color: #1c1c1c;
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .container {
      display: flex;
      width: 750px;
      height: 420px;
      background-color: #2a2a2a;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 10px 25px rgba(0,0,0,0.4);
    }

    .left {
      width: 50%;
      background-color: #000;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 20px;
    }

    .left img {
      width: 90%;
      max-height: 300px;
      object-fit: contain;
    }

    .right {
      width: 50%;
      background-color: #2a2a2a;
      padding: 40px 30px;
      color: #f5c518;
      position: relative;
    }

    h2 {
      text-align: center;
      margin-bottom: 25px;
      font-size: 24px;
    }

    .input-group {
      margin-bottom: 18px;
      position: relative;
    }

    label {
      display: block;
      font-weight: 500;
      margin-bottom: 6px;
      color: #ccc;
    }

    input {
      width: 100%;
      padding: 10px 40px 10px 10px;
      border: 1px solid #555;
      border-radius: 6px;
      background-color: #1e1e1e;
      color: white;
      font-size: 14px;
    }

    input:focus {
      outline: none;
      border-color: #f5c518;
    }

    .toggle-password {
      position: absolute;
      right: 10px;
      top: 37px;
      color: #ccc;
      cursor: pointer;
    }

    .links {
      display: flex;
      justify-content: space-between;
      font-size: 13px;
      margin-top: 5px;
    }

    .links a {
      text-decoration: none;
      color: #f5c518;
    }

    .login-btn {
      width: 100%;
      background: #f5c518;
      color: #1c1c1c;
      padding: 12px;
      border: none;
      border-radius: 6px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      margin-top: 20px;
      transition: background 0.3s;
    }

    .login-btn:hover {
      background: #d4a900;
    }

    @media (max-width: 768px) {
      .container {
        flex-direction: column;
        height: auto;
        width: 90%;
      }

      .left, .right {
        width: 100%;
      }

      .left {
        padding: 30px 10px;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="left">
      <img src="../assets/kasir.jpg" alt="Kasir Logo">
    </div>
    <div class="right">
      <h2>Sign in Admin</h2>
      <form method="POST">
        <div class="input-group">
          <label>Username</label>
          <input type="text" name="username" required>
        </div>
        <div class="input-group">
          <label>Password</label>
          <input type="password" name="password" id="password" required>
          <span class="toggle-password" onclick="togglePassword()">
            <i class="fa-solid fa-eye" id="eye-icon"></i>
          </span>
        </div>
        <div class="links">
          
          <a href="../service/pass.php">Lupa password?</a>
        </div>
        <button type="submit" class="login-btn">Login</button>
      </form>
    </div>
  </div>

  <script>
    function togglePassword() {
      const passwordField = document.getElementById('password');
      const eyeIcon = document.getElementById('eye-icon');

      if (passwordField.type === 'password') {
        passwordField.type = 'text';
        eyeIcon.classList.remove('fa-eye');
        eyeIcon.classList.add('fa-eye-slash');
      } else {
        passwordField.type = 'password';
        eyeIcon.classList.remove('fa-eye-slash');
        eyeIcon.classList.add('fa-eye');
      }
    }
  </script>
</body>
</html>

