<?php
require 'conection.php'; // koneksi database

session_start();

if (isset($_GET['token'])) {
    $token = mysqli_real_escape_string($conn, $_GET['token']);

    // Cek token valid dan belum expired
    $query = "SELECT * FROM admin WHERE reset_token='$token' AND reset_expiry > NOW()";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) === 1) {
        $admin = mysqli_fetch_assoc($result);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = mysqli_real_escape_string($conn, $_POST['password']);
            $confirm  = mysqli_real_escape_string($conn, $_POST['confirm']);

            if (strlen($password) < 6) {
                $error = "❌ Password minimal 6 karakter.";
            } elseif ($password !== $confirm) {
                $error = "❌ Konfirmasi password tidak cocok.";
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $update = "UPDATE admin 
                           SET password='$hashed', reset_token=NULL, reset_expiry=NULL 
                           WHERE id=" . $admin['id'];
                if (mysqli_query($conn, $update)) {
                    $success = "✅ Password berhasil diubah. <a href='../admin/index.php'>Login di sini</a>";
                } else {
                    $error = "❌ Terjadi kesalahan saat menyimpan password.";
                }
            }
        }
    } else {
        $error = "❌ Token tidak valid atau sudah kedaluwarsa.";
    }
} else {
    $error = "❌ Token tidak ditemukan.";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Reset Password</title>
    <style>
        body { font-family: Arial; background: #f4f6f9; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .container { background: #fff; padding: 25px; border-radius: 10px; width: 400px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { color: #0044cc; }
        input[type="password"], button { width: 100%; padding: 10px; margin: 10px 0; font-size: 16px; border-radius: 5px; }
        button { background-color: #0044cc; color: white; border: none; cursor: pointer; }
        button:hover { background-color: #003399; }
        .error { color: red; font-size: 14px; }
        .success { color: green; font-size: 14px; }
        a { color: #0044cc; text-decoration: none; }
    </style>
</head>
<body>
<div class="container">
    <h2>Reset Password</h2>

    <?php if (isset($error)): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php elseif (isset($success)): ?>
        <p class="success"><?= $success ?></p>
    <?php elseif (isset($admin)): ?>
        <form method="POST" action="">
            <input type="password" name="password" placeholder="Password baru" required>
            <input type="password" name="confirm" placeholder="Konfirmasi password" required>
            <button type="submit">Simpan Password</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
