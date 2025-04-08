<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kasir Toko Elektronik</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            text-align: center;
            background-color: #f4f4f4;
        }
        header {
            background: #007bff;
            color: white;
            padding: 50px 20px;
            position: relative;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            color: white;
            background: #0056b3;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        #fitur {
            padding: 50px 20px;
            background: white;
        }
        .features {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        .feature-box {
            background: #ddd;
            padding: 20px;
            border-radius: 10px;
            width: 250px;
        }
        footer {
            background: #333;
            color: white;
            padding: 20px;
            margin-top: 20px;
        }
        .login-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 10px 15px;
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <header>
        <a href="Admin/index.php" class="login-btn">Login</a>
        <h1>Kasir Toko Elektronik</h1>
        <p>Solusi modern untuk pengelolaan transaksi toko elektronik Anda</p>
        <a href="#fitur" class="btn">Pelajari Lebih Lanjut</a>
    </header>
    
    <section id="fitur">
        <h2>Fitur Unggulan</h2>
        <div class="features">
            <div class="feature-box">
                <h3>Manajemen Stok</h3>
                <p>Kelola stok barang dengan mudah dan akurat.</p>
            </div>
            <div class="feature-box">
                <h3>Transaksi Cepat</h3>
                <p>Proses transaksi lebih cepat dengan sistem otomatis.</p>
            </div>
            <div class="feature-box">
                <h3>Laporan Keuangan</h3>
                <p>Analisis keuangan dengan laporan harian, mingguan, dan bulanan.</p>
            </div>
        </div>
    </section>
    
    <footer>
        <p>&copy; 2025 Kasir Toko Elektronik. Semua Hak Dilindungi.</p>
    </footer>
</body>
</html>
