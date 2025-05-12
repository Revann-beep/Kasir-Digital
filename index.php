<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kasir Toko Elektronik</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            text-align: center;
            background-color: #f0f0f0;
            color: #333;
        }
        header {
            background: url('background.jpg') no-repeat center center/cover;
            color: white;
            padding: 120px 20px;
            position: relative;
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.6);
        }
        header h1 {
            font-size: 3em;
            font-weight: 700;
            margin-bottom: 20px;
        }
        header p {
            font-size: 1.3em;
            margin-bottom: 30px;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            color: white;
            background: #007bff;
            text-decoration: none;
            border-radius: 50px;
            font-size: 1.1em;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #0056b3;
        }
        .login-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 12px 18px;
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 1em;
        }
        .login-btn:hover {
            background: #218838;
        }
        #fitur {
            padding: 50px 20px;
            background-color: #ffffff;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
        }
        #fitur h2 {
            font-size: 2.5em;
            margin-bottom: 40px;
        }
        .features {
            display: flex;
            justify-content: space-around;
            gap: 20px;
            flex-wrap: wrap;
        }
        .feature-box {
            background: #007bff;
            color: white;
            padding: 20px;
            border-radius: 10px;
            width: 280px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }
        .feature-box:hover {
            transform: scale(1.05);
        }
        .feature-box h3 {
            font-size: 1.7em;
            margin-bottom: 15px;
        }
        .feature-box p {
            font-size: 1.1em;
            line-height: 1.6;
        }
        footer {
            background: #333;
            color: white;
            padding: 20px;
            margin-top: 30px;
            font-size: 1.1em;
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
                <p>Kelola stok barang dengan mudah dan akurat, terhubung langsung dengan sistem kasir.</p>
            </div>
            <div class="feature-box">
                <h3>Transaksi Cepat</h3>
                <p>Proses transaksi lebih cepat dan mudah dengan sistem otomatis dan antarmuka yang ramah pengguna.</p>
            </div>
            <div class="feature-box">
                <h3>Laporan Keuangan</h3>
                <p>Analisis keuangan dengan laporan harian, mingguan, dan bulanan untuk keputusan yang lebih tepat.</p>
            </div>
        </div>
    </section>
    
    <footer>
        <p>&copy; 2025 Kasir Toko Elektronik. Semua Hak Dilindungi.</p>
    </footer>
</body>
</html>
