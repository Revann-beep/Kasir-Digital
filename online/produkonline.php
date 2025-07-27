<?php
session_start();
include '../service/conection.php';

// Waktu expired keranjang (1 jam)
$waktu_expired = 60 * 60;
if (isset($_SESSION['keranjang_waktu']) && time() - $_SESSION['keranjang_waktu'] > $waktu_expired) {
    unset($_SESSION['keranjang']);
    unset($_SESSION['keranjang_waktu']);
    $_SESSION['alert'] = "Keranjang dihapus karena lebih dari 1 jam.";
    header("Location: produkonline.php");
    exit;
}

// Inisialisasi keranjang
if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}

// Tambah produk
if (isset($_GET['tambah']) && is_numeric($_GET['tambah'])) {
    $id = (int) $_GET['tambah'];

    // Cek stok dari database
    $q = mysqli_query($conn, "SELECT stok FROM produk WHERE id_produk=$id");
    $produk = mysqli_fetch_assoc($q);
    $stok = $produk['stok'] ?? 0;

    if (isset($_SESSION['keranjang'][$id])) {
        if ($_SESSION['keranjang'][$id] < $stok) {
            $_SESSION['keranjang'][$id]++;
        } else {
            $_SESSION['alert'] = "Stok produk tidak mencukupi.";
        }
    } else {
        if (count($_SESSION['keranjang']) >= 5) {
            $_SESSION['alert'] = "Maksimal 5 produk berbeda di keranjang.";
        } else {
            $_SESSION['keranjang'][$id] = 1;
        }
    }

    if (!isset($_SESSION['keranjang_waktu'])) {
        $_SESSION['keranjang_waktu'] = time();
    }

    header("Location: produkonline.php");
    exit;
}

// Kurangi produk
if (isset($_GET['kurang']) && is_numeric($_GET['kurang'])) {
    $id = (int) $_GET['kurang'];
    if (isset($_SESSION['keranjang'][$id])) {
        $_SESSION['keranjang'][$id]--;
        if ($_SESSION['keranjang'][$id] <= 0) {
            unset($_SESSION['keranjang'][$id]);
        }
    }
    header("Location: produkonline.php");
    exit;
}

// Hapus produk
if (isset($_GET['hapus']) && is_numeric($_GET['hapus'])) {
    unset($_SESSION['keranjang'][(int)$_GET['hapus']]);
    header("Location: produkonline.php");
    exit;
}

// Ambil semua produk
$query = mysqli_query($conn, "SELECT * FROM produk WHERE stok > 0");

// Produk dalam keranjang
$keranjangProduk = [];
if (!empty($_SESSION['keranjang'])) {
    $ids = implode(",", array_keys($_SESSION['keranjang']));
    $keranjangQuery = mysqli_query($conn, "SELECT * FROM produk WHERE id_produk IN ($ids)");
    while ($row = mysqli_fetch_assoc($keranjangQuery)) {
        $id = $row['id_produk'];
        $row['jumlah'] = $_SESSION['keranjang'][$id];
        $keranjangProduk[] = $row;
    }
}

$jumlahKeranjang = array_sum($_SESSION['keranjang']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Produk</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <style>
        /* [CSS sama seperti sebelumnya — tidak diubah] */
        /* Paste your full existing CSS here */
        body { font-family: 'Poppins', sans-serif; background-color: #000000ff; padding: 40px 20px; }
        .btn-kembali {
            position: fixed; top: 20px; left: 20px; background-color: #e0e0e0;
            color: #333; padding: 8px 14px; border-radius: 8px;
            text-decoration: none; font-weight: bold; box-shadow: 0 2px 5px rgba(0,0,0,0.2); z-index: 999;
        }
        .cart-button {
            position: fixed; top: 20px; right: 20px; font-size: 24px;
            color: #333; cursor: pointer; z-index: 999;
        }
        .cart-button span {
            position: absolute; top: -8px; right: -10px;
            background-color: red; color: white;
            font-size: 12px; padding: 2px 6px; border-radius: 50%;
        }
        .keranjang-container {
            display: none; position: absolute; top: 60px; right: 20px;
            width: 300px; background: white; box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            padding: 15px; border-radius: 8px; z-index: 998;
        }
        .keranjang-container h3 { margin-bottom: 10px; font-size: 16px; }
        .keranjang-item { font-size: 14px; margin-bottom: 6px; }
        .keranjang-item span { float: right; cursor: pointer; color: red; }
        .keranjang-total { font-weight: bold; margin-top: 10px; }
        .produk-container {
            margin-top: 120px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 20px;
        }
        .card {
            background: #fff; border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            overflow: hidden; transition: transform 0.2s ease;
        }
        .card:hover { transform: translateY(-5px); }
        .card img {
            width: 100%; height: 160px;
            object-fit: contain; background-color: #f0f0f0;
            padding: 10px; border-bottom: 1px solid #eee;
        }
        .card-body { padding: 15px; }
        .card-title { font-size: 18px; font-weight: 600; margin-bottom: 8px; }
        .card-price { color: #e91e63; font-size: 16px; margin-bottom: 8px; }
        .card-stock { font-size: 14px; color: #555; margin-bottom: 12px; }
        .btn-beli {
            display: inline-block; background: #007bff; color: #fff;
            padding: 8px 16px; border: none; border-radius: 8px;
            cursor: pointer; text-decoration: none;
        }
        .btn-beli:hover { background: #0056b3; }
        .btn-checkout {
            display: inline-block; margin-top: 12px;
            background-color: green; color: white;
            padding: 6px 12px; border-radius: 6px;
            text-decoration: none;
        }
        .btn-qty {
            margin: 0 5px; padding: 2px 6px;
            background-color: #ddd; text-decoration: none;
            border-radius: 4px; font-weight: bold;
        }
    </style>
</head>
<body>

<?php if (isset($_SESSION['alert'])): ?>
    <script>alert("<?= $_SESSION['alert'] ?>");</script>
    <?php unset($_SESSION['alert']); ?>
<?php endif; ?>

<a href="../admin/produk.php" class="btn-kembali">← Kembali</a>

<div class="cart-button" onclick="toggleKeranjang()">
    <i class="fas fa-shopping-cart"></i>
    <span id="cart-count"><?= $jumlahKeranjang ?></span>
</div>

<div class="keranjang-container" id="keranjang">
    <h3>Keranjang</h3>
    <?php if (empty($keranjangProduk)) : ?>
        <p>Keranjang kosong</p>
    <?php else:
        $total = 0;
        foreach ($keranjangProduk as $item):
            $subtotal = $item['harga_jual'] * $item['jumlah'];
            $total += $subtotal;
        ?>
            <div class="keranjang-item">
                <?= htmlspecialchars($item['nama_produk']) ?> (<?= $item['jumlah'] ?>)
                <a href="?kurang=<?= $item['id_produk'] ?>" class="btn-qty">−</a>
                <a href="?tambah=<?= $item['id_produk'] ?>" class="btn-qty">+</a>
                <a href="?hapus=<?= $item['id_produk'] ?>"><span>&times;</span></a>
            </div>
        <?php endforeach; ?>
        <div class="keranjang-total">Total: Rp <?= number_format($total, 0, ',', '.') ?></div>
        <a href="checkout.php" class="btn-checkout">Checkout</a>
    <?php endif; ?>
</div>

<h1>Daftar Produk</h1>
<div class="produk-container">
    <?php while ($row = mysqli_fetch_assoc($query)) : ?>
        <div class="card">
            <img src="../assets/<?= $row['gambar'] ?>" alt="<?= htmlspecialchars($row['nama_produk']) ?>">
            <div class="card-body">
                <div class="card-title"><?= htmlspecialchars($row['nama_produk']) ?></div>
                <div class="card-price">Rp <?= number_format($row['harga_jual'], 0, ',', '.') ?></div>
                <div class="card-stock">Stok: <?= $row['stok'] ?></div>
                <div class="card-desc"><?= htmlspecialchars($row['deskripsi']) ?></div>
                <br>
                <a href="?tambah=<?= $row['id_produk'] ?>" class="btn-beli">Beli Sekarang</a>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<script>
function toggleKeranjang() {
    const box = document.getElementById("keranjang");
    box.style.display = box.style.display === "block" ? "none" : "block";
}
</script>

</body>
</html>
