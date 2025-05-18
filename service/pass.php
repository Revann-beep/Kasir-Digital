<?php
require 'conection.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    // Cek apakah email ada di database
    $query = "SELECT * FROM admin WHERE email='$email'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $token = bin2hex(random_bytes(32)); // Generate token unik
        $expiry = date("Y-m-d H:i:s", strtotime("+1 hour")); // Berlaku 1 jam
        
        // Simpan token di database
        $updateQuery = "UPDATE admin SET reset_token='$token', reset_expiry='$expiry' WHERE email='$email'";
        mysqli_query($conn, $updateQuery);
        
        // Kirim link reset password (simulasi)
        $resetLink = "http://localhost/kasir/service/resetpass.php?token=$token";
        $_SESSION['message'] = "<p class='success'>✅ Silakan klik link berikut untuk reset password: <a href='$resetLink'>$resetLink</a></p>";
    } else {
        $_SESSION['message'] = "<p class='error'>❌ Email tidak ditemukan!</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password</title>
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
input[type="email"] {
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

/* Link kembali ke login */
a {
    display: block;
    margin-top: 10px;
    color: #0044cc;
    text-decoration: none;
}

a:hover {
    text-decoration: underline;
}
</style>
</head>
<body>
    <div class="container">
        <h2>Lupa Password</h2>
        <p>Masukkan email Anda untuk mereset password.</p>
        
        <?php
        if (isset($_SESSION['message'])) {
            echo $_SESSION['message'];
            unset($_SESSION['message']); // Hapus pesan setelah ditampilkan
        }
        ?>

        <form method="POST">
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" placeholder="Masukkan email" required>
            <button type="submit">Reset Password</button>
        </form>
        
        <p><a href="../admin/index.php">Kembali ke Login</a></p>
    </div>
</body>
</html>