<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Toko Jam Online</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0; padding: 0; box-sizing: border-box;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f4f4;
            color: #333;
        }
        header {
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .hero-image {
            width: 100%;
            max-height: 80vh;
            object-fit: cover;
            display: block;
        }
        .hero-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: white;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.6);
    z-index: 2;
    padding: 30px;
    background-color: rgba(0, 0, 0, 0.5); /* Tambahan: latar belakang transparan */
    border-radius: 12px; /* Tambahan: sudut membulat */
    max-width: 90%; /* Biar tidak terlalu lebar */
    box-shadow: 0 4px 12px rgba(0,0,0,0.3); /* Tambahan: efek bayangan */
    text-align: center;
}

        .hero-content h1 {
            font-size: 3rem;
            margin-bottom: 10px;
        }
        .hero-content p {
            font-size: 1.2rem;
            margin-bottom: 30px;
        }
        .btn-lihat {
            background-color: #ff9900;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            text-decoration: none;
            transition: background 0.3s;
        }
        .btn-lihat:hover {
            background-color: #e08600;
        }
        section {
            padding: 60px 20px;
            text-align: center;
            background: white;
        }
        section h2 {
            font-size: 2rem;
            margin-bottom: 20px;
        }
        section p {
            max-width: 700px;
            margin: 0 auto;
            font-size: 1.1rem;
        }

        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 2rem;
            }
            .hero-content p {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>

    <header>
        <img src="./assets/jam.jpg" alt="Jam Tangan" class="hero-image">
        <div class="hero-content">
            <h1>Toko Jam Online</h1>
            <p>Jam berkualitas, gaya elegan, harga terjangkau.</p>
            <a href="./online/produkonline.php" class="btn-lihat">Lihat Produk</a>
        </div>
    </header>

    <section>
        <h2>Tentang Kami</h2>
        <p>Kami menjual berbagai jenis jam tangan pria dan wanita. Dari jam digital sporty hingga jam analog elegan. Produk kami terjamin kualitas dan keasliannya.</p>
    </section>

</body>
</html>
