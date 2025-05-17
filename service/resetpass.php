<?php
require 'conection.php'; // Pastikan koneksi ke database benar

if (!isset($_GET['token'])) {
    die("<p class='error'>❌ Token tidak ditemukan!</p>");
}

$token = trim($_GET['token']); // Bersihkan token dari spasi yang tidak perlu

// Cek token dan waktu kadaluarsa
$query = "SELECT email, reset_token, reset_expiry FROM admin WHERE reset_token = ?";
$stmt = mysqli_prepare($conn, $query);

// Cek apakah prepare berhasil
if (!$stmt) {
    die("<p class='error'>❌ Query gagal diproses: " . mysqli_error($conn) . "</p>");
}

mysqli_stmt_bind_param($stmt, "s", $token);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result) {
    die("<p class='error'>❌ Query gagal dijalankan: " . mysqli_error($conn) . "</p>");
}

if (mysqli_num_rows($result) == 1) {
    $row = mysqli_fetch_assoc($result);
    $email = $row['email'];
    $db_token = trim($row['reset_token']);
    $reset_expiry = strtotime($row['reset_expiry']);
    $current_time = time();

    if ($reset_expiry < $current_time) {
        die("<p class='error'>❌ Token sudah kedaluwarsa!</p>");
    }

    if ($db_token !== $token) {
        die("<p class='error'>❌ Token tidak cocok!</p>");
    }
} else {
    die("<p class='error'>❌ Token tidak valid atau sudah kedaluwarsa!</p>");
}


    // Jika token valid, tampilkan form reset password
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Reset Password</title>
        <link rel="stylesheet" href="style.css">
        <style>/* Reset default margin dan padding */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

/* Styling halaman */
body {
    background-color: #f0f5ff; /* Warna latar belakang lembut */
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

/* Kontainer utama */
.container {
    background: #ffffff;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.1);
    width: 400px;
    text-align: center;
}

/* Judul */
h2 {
    color: #0044cc;
    margin-bottom: 10px;
}

/* Paragraf */
p {
    color: #555;
    margin-bottom: 15px;
}

/* Input fields */
input[type="password"] {
    width: 100%;
    padding: 10px;
    margin: 8px 0;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 16px;
}

/* Tombol reset */
button {
    width: 100%;
    background-color: #0044cc;
    color: white;
    padding: 12px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    margin-top: 10px;
}

button:hover {
    background-color: #003399;
}

/* Pesan error & sukses */
.error {
    color: red;
    font-size: 14px;
    margin-top: 10px;
}

.success {
    color: green;
    font-size: 14px;
    margin-top: 10px;
}
</style>
    </head>
    <body>
        <div class="container">
            <h2>Reset Password</h2>
            <p>Masukkan password baru untuk akun: <strong><?php echo $email; ?></strong></p>

            <form method="POST">
                <input type="password" name="password" placeholder="Password Baru" required>
                <input type="password" name="konfirmasi" placeholder="Konfirmasi Password" required>
                <button type="submit" name="reset">Reset Password</button>
            </form>

            <?php
            // Proses reset password jika form dikirimkan
            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reset'])) {
    if (isset($_POST['password']) && isset($_POST['konfirmasi'])) {
        $password = $_POST['password'];
        $konfirmasi = $_POST['konfirmasi'];

        if ($password !== $konfirmasi) {
            echo "<p class='error'>❌ Password tidak cocok!</p>";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT); // Hash password baru

            $update = "UPDATE admin SET password = ?, reset_token = NULL, reset_expiry = NULL WHERE email = ?";
            $stmt = mysqli_prepare($conn, $update);
            
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "ss", $hash, $email);
                if (mysqli_stmt_execute($stmt)) {
                    echo "<p class='success'>✅ Password berhasil direset! Silakan <a href='../admin/index.php'>login</a>.</p>";
                } else {
                    echo "<p class='error'>❌ Gagal mereset password: " . mysqli_error($conn) . "</p>";
                }
            } else {
                echo "<p class='error'>❌ Query gagal diproses: " . mysqli_error($conn) . "</p>";
            }
        }
    } else {
        echo "<p class='error'>❌ Harap isi semua kolom!</p>";
    }
}

?>