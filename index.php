<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Kasir Toko Jam Tangan</title>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css"/>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Outfit', sans-serif;
      background-color: #f3f4f6;
      color: #333;
    }

    header {
      background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.7)), url('jam-header.jpg') center/cover no-repeat;
      color: white;
      text-align: center;
      padding: 160px 20px 120px;
      position: relative;
    }

    .login-btn {
      position: absolute;
      top: 20px;
      right: 30px;
      background-color: #ffc107;
      padding: 10px 20px;
      border-radius: 30px;
      font-weight: 600;
      font-size: 1em;
      text-decoration: none;
      color: #212529;
      transition: background 0.3s;
    }

    .login-btn:hover {
      background-color: #e0a800;
    }

    header h1 {
      font-size: 3.2em;
      margin-bottom: 15px;
    }

    header p {
      font-size: 1.4em;
      margin-bottom: 30px;
      opacity: 0.9;
    }

    .btn {
      background-color: #007bff;
      color: white;
      padding: 14px 32px;
      border-radius: 50px;
      text-decoration: none;
      font-size: 1.1em;
      transition: background 0.3s;
    }

    .btn:hover {
      background-color: #0056b3;
    }

    section#fitur {
      padding: 80px 20px;
      background: #fff;
    }

    #fitur h2 {
      font-size: 2.5em;
      text-align: center;
      margin-bottom: 60px;
      color: #0d6efd;
    }

    .features {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 30px;
    }

    .feature-box {
      background: #ffffff;
      border: 2px solid #0d6efd;
      border-radius: 16px;
      padding: 30px 25px;
      width: 300px;
      text-align: center;
      transition: 0.3s ease;
    }

    .feature-box:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(13, 110, 253, 0.1);
    }

    .feature-box i {
      font-size: 40px;
      color: #0d6efd;
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
      background: #1e1e2f;
      color: white;
      text-align: center;
      padding: 25px 20px;
      font-size: 1em;
    }

    @media (max-width: 768px) {
      header h1 {
        font-size: 2.2em;
      }

      .feature-box {
        width: 90%;
      }

      .login-btn {
        top: 15px;
        right: 15px;
        padding: 8px 16px;
      }
    }
  </style>
</head>
<body>

  <header>
    <a href="Admin/index.php" class="login-btn">Login</a>
    <h1>Kasir Toko Jam Tangan</h1>
    <p>Kelola penjualan & stok toko jam tangan Anda secara modern dan otomatis</p>
    <a href="#fitur" class="btn">Lihat Fitur</a>
  </header>

  <section id="fitur">
    <h2 data-aos="fade-up">Fitur Utama</h2>
    <div class="features">
      <div class="feature-box" data-aos="fade-up" data-aos-delay="100">
        <i class="fas fa-boxes"></i>
        <h3>Manajemen Stok</h3>
        <p>Pantau stok jam secara real-time dan hindari kehabisan barang.</p>
      </div>
      <div class="feature-box" data-aos="fade-up" data-aos-delay="200">
        <i class="fas fa-cash-register"></i>
        <h3>Transaksi Mudah</h3>
        <p>Proses penjualan cepat dan efisien hanya dalam beberapa klik.</p>
      </div>
      <div class="feature-box" data-aos="fade-up" data-aos-delay="300">
        <i class="fas fa-users"></i>
        <h3>Member & Poin</h3>
        <p>Berikan poin otomatis & diskon menarik untuk pelanggan setia.</p>
      </div>
      <div class="feature-box" data-aos="fade-up" data-aos-delay="400">
        <i class="fas fa-chart-bar"></i>
        <h3>Laporan Lengkap</h3>
        <p>Rekap penjualan, keuntungan, dan performa toko harian hingga bulanan.</p>
      </div>
    </div>
  </section>

  <footer>
    <p>&copy; 2025 Kasir Toko Jam Tangan. Dibuat dengan ❤️ untuk UMKM Indonesia.</p>
  </footer>

  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
  <script>
    AOS.init();
  </script>

</body>
</html>
