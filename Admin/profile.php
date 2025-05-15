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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Profil Admin - TimelessWatch.co</title>
    <style>
        /* Reset & base */
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            background: linear-gradient(135deg, #f5f7fa, #c3cfe2);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            color: #333;
        }

        .profile-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
            max-width: 420px;
            width: 100%;
            padding: 40px 30px;
            text-align: center;
            transition: transform 0.3s ease;
        }
        .profile-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 40px rgba(0,0,0,0.15);
        }

        .profile-img {
            width: 130px;
            height: 130px;
            margin: 0 auto 25px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #3498db;
            box-shadow: 0 4px 8px rgba(52, 152, 219, 0.4);
            background-color: #fff;
        }

        h2 {
            margin-bottom: 15px;
            font-size: 28px;
            font-weight: 700;
            color: #2c3e50;
        }

        .profile-item {
            text-align: left;
            margin-bottom: 20px;
            font-size: 16px;
            padding: 0 10px;
        }
        .profile-item label {
            display: block;
            font-weight: 600;
            color: #555;
            margin-bottom: 6px;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            font-size: 12px;
        }
        .profile-item div {
            background: #f0f4f8;
            padding: 10px 14px;
            border-radius: 8px;
            color: #444;
            font-weight: 500;
            box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
            user-select: text;
            word-wrap: break-word;
        }

        .back-link {
            display: inline-block;
            margin-top: 30px;
            padding: 12px 24px;
            background: #3498db;
            color: white;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
            transition: background-color 0.3s ease;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
            user-select: none;
        }
        .back-link:hover {
            background: #2980b9;
            box-shadow: 0 6px 25px rgba(41, 128, 185, 0.5);
        }

        /* Responsive */
        @media (max-width: 480px) {
            .profile-card {
                padding: 30px 20px;
            }
            h2 {
                font-size: 24px;
            }
            .profile-item {
                font-size: 14px;
            }
            .back-link {
                font-size: 14px;
                padding: 10px 20px;
            }
        }
    </style>
</head>
<body>

<div class="profile-card">
    <img src="../assets/<?php echo htmlspecialchars($gambar); ?>" alt="Foto Profil Admin" class="profile-img" />
    <h2><?php echo htmlspecialchars($data['username']); ?></h2>

    <div class="profile-item">
        <label>Email</label>
        <div><?php echo htmlspecialchars($data['email']); ?></div>
    </div>
    <div class="profile-item">
        <label>Status</label>
        <div><?php echo htmlspecialchars($data['status']); ?></div>
    </div>

    <a href="dashboard.php" class="back-link">← Kembali ke Dashboard</a>
</div>

</body>
</html>
