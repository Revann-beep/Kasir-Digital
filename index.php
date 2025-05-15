<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Kasir Toko Elektronik</title>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css"/>
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      margin: 0;
      background-color: #f8f9fa;
      color: #333;
      scroll-behavior: smooth;
    }

    header {
      background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.6)), url('background.jpg') center/cover no-repeat;
      color: white;
      padding: 150px 20px 120px;
      text-align: center;
    }

    header h1 {
      font-size: 3.2em;
      margin-bottom: 15px;
    }

    header p {
      font-size: 1.4em;
      margin-bottom: 30px;
    }

    .btn {
      background-color: #007bff;
      color: white;
      padding: 12px 30px;
      font-size: 1.1em;
      border-radius: 50px;
      text-decoration: none;
      transition: background 0.3s;
    }

    .btn:hover {
      background-color: #0056b3;
    }

    .login-btn {
      position: absolute;
      top: 20px;
      right: 20px;
      background-color: #28a745;
      padding: 10px 18px;
      border-radius: 5px;
      font-weight: 600;
      font-size: 1em;
      text-decoration: none;
      color: white;
    }

    .login-btn:hover {
      background-color: #218838;
    }

    #fitur {
      padding: 70px 20px;
      background: #fff;
    }

    #fitur h2 {
      font-size: 2.5em;
      margin-bottom: 50px;
      text-align: center;
      color: #007bff;
    }

    .features {
      display: flex;
      justify-content: center;
      gap: 30px;
      flex-wrap: wrap;
    }

    .feature-box {
      background: #ffffff;
      border: 2px solid #007bff;
      border-radius: 12px;
      padding: 30px 20px;
      width: 280px;
      text-align: center;
      transition: transform 0.3s, box-shadow 0.3s;
    }

    .feature-box:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(0, 123, 255, 0.1);
    }

    .feature-box i {
      font-size: 40px;
      color: #007bff;
      margin-bottom: 15px;
    }

    .feature-box h3 {
      font-size: 1.5em;
      margin-bottom: 10px;
    }

    .feature-box p {
      font-size: 1.05em;
      line-height: 1.6;
      color: #555;
    }

    footer {
      background: #343a40;
      color: white;
      padding: 25px 20px;
      text-align: center;
      font-size: 1em;
      margin-top: 50px;
    }

    @media (max-width: 768px) {
      header h1 {
        font-size: 2.3em;
      }

      .feature-box {
        width: 90%;
      }
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
    <h2 data-aos="fade-up">Fitur Unggulan</h2>
    <div class="features">
      <div class="feature-box" data-aos="fade-up" data-aos-delay="100">
        <i class="fas fa-boxes"></i>
        <h3>Manajemen Stok</h3>
        <p>Kelola stok barang secara real-time dan akurat, langsung dari dashboard kasir Anda.</p>
      </div>
      <div class="feature-box" data-aos="fade-up" data-aos-delay="200">
        <i class="fas fa-bolt"></i>
        <h3>Transaksi Cepat</h3>
        <p>Proses transaksi pelanggan dalam hitungan detik dengan sistem otomatis yang efisien.</p>
      </div>
      <div class="feature-box" data-aos="fade-up" data-aos-delay="300">
        <i class="fas fa-chart-line"></i>
        <h3>Laporan Keuangan</h3>
        <p>Lihat laporan penjualan harian, mingguan, dan bulanan untuk memudahkan analisis bisnis.</p>
      </div>
    </div>
  </section>

  <footer>
    <p>&copy; 2025 Kasir Toko Elektronik. Semua Hak Dilindungi.</p>
  </footer>

  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
  <script>
    AOS.init();
  </script>

</body>
</html>
