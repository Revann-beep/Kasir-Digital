<?php
require 'conection.php';

// Include manual PHPMailer tanpa autoload
require '../assets/PHPMailer/PHPMailer.php';
require '../assets/PHPMailer/SMTP.php';
require '../assets/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    // Cek email
    $query = "SELECT * FROM admin WHERE email='$email'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $token = bin2hex(random_bytes(32));
        $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

        $update = "UPDATE admin SET reset_token='$token', reset_expiry='$expiry' WHERE email='$email'";
        mysqli_query($conn, $update);

        $resetLink = "http://localhost/kasir/service/resetpass.php?token=$token";

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'reifanevandra8@gmail.com'; // Ganti dengan Gmail kamu
            $mail->Password   = 'fewt wnjm wbzb whou'; // Ganti dengan App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('reifanevandra8@gmail.com', 'kasir-php');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Reset Password - kasir-php';
            $mail->Body    = "
                <p>Halo,</p>
                <p>Silakan klik link berikut untuk mengatur ulang password Anda:</p>
                <p><a href='$resetLink'>$resetLink</a></p>
                <p>Link ini hanya berlaku selama 1 jam.</p>
            ";

            $mail->send();
            $_SESSION['message'] = "<p class='success'>✅ Link reset telah dikirim ke email Anda.</p>";
        } catch (Exception $e) {
            $_SESSION['message'] = "<p class='error'>❌ Gagal kirim email: {$mail->ErrorInfo}</p>";
        }
    } else {
        $_SESSION['message'] = "<p class='error'>❌ Email tidak ditemukan.</p>";
    }
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Lupa Password</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { font-family: Arial; background: #f4f6f9; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .container { background: #fff; padding: 25px; border-radius: 10px; width: 400px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { color: #0044cc; }
        input[type="email"], button { width: 100%; padding: 10px; margin: 10px 0; font-size: 16px; border-radius: 5px; }
        button { background-color: #0044cc; color: white; border: none; cursor: pointer; }
        button:hover { background-color: #003399; }
        .error { color: red; font-size: 14px; }
        .success { color: green; font-size: 14px; }
        a { text-decoration: none; color: #0044cc; }
    </style>
</head>
<body>
<div class="container">
    <h2>Lupa Password</h2>
    <p>Masukkan email Anda untuk mengatur ulang password.</p>

    <?php
    if (isset($_SESSION['message'])) {
        echo $_SESSION['message'];
        unset($_SESSION['message']);
    }
    ?>

    <form method="POST">
        <input type="email" name="email" placeholder="Masukkan email Anda" required>
        <button type="submit">Kirim Link Reset</button>
    </form>

    <p><a href="../admin/index.php">← Kembali ke Login</a></p>
</div>
</body>
</html>
