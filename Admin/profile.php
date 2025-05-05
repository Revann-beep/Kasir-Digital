<?php
session_start();
include '../service/conection.php';

if (!isset($_SESSION['username'])) {
    header("Location: ../service/index.php");
    exit;
}

$username = $_SESSION['username'];
$query = mysqli_query($conn, "SELECT * FROM admin WHERE username = '$username'");
$data = mysqli_fetch_assoc($query);

// Default gambar jika kosong
$gambar = !empty($data['gambar']) ? $data['gambar'] : 'default.jpg';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Profil Admin</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            padding: 40px;
        }
        .profile-card {
            max-width: 500px;
            margin: auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            padding: 30px;
            text-align: center;
        }
        .profile-card img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin-bottom: 20px;
            object-fit: cover;
        }
        .profile-item {
            margin-bottom: 15px;
            text-align: left;
        }
        .profile-item label {
            font-weight: bold;
        }
        .back-link {
            display: block;
            margin-top: 30px;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="profile-card">
    <img src="../assets/<?php echo htmlspecialchars($gambar); ?>" alt="gambar Profil">
    <h2><?php echo htmlspecialchars($data['username']); ?></h2>
    
    <div class="profile-item">
        <label>Email:</label>
        <div><?php echo htmlspecialchars($data['email']); ?></div>
    </div>
    <div class="profile-item">
        <label>Status:</label>
        <div><?php echo htmlspecialchars($data['status']); ?></div>
    </div>

    <a class="back-link" href="dashboard.php">← Kembali ke Dashboard</a>
</div>

</body>
</html>
