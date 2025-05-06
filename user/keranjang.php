<?php
session_start();

// === [TAMBAHAN: Set dan periksa masa berlaku keranjang] ===
$waktu_expired = 10 * 60; // 15 menit

if (isset($_SESSION['keranjang'])) {
    if (!isset($_SESSION['keranjang_waktu'])) {
        $_SESSION['keranjang_waktu'] = time();
    } else {
        if (time() - $_SESSION['keranjang_waktu'] > $waktu_expired) {
            unset($_SESSION['keranjang']);
            unset($_SESSION['keranjang_waktu']);
            echo "<script>alert('Waktu checkout habis. Keranjang dikosongkan.'); location.reload();</script>";
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Keranjang Belanja</title>
    <style>
        /* (STYLE TIDAK DIUBAH) */
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #f5f7fa;
            color: #333;
        }
        .container {
            max-width: 960px;
            margin: 40px auto;
            padding: 20px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        }
        h2 {
            text-align: center;
            font-size: 28px;
            margin-bottom: 30px;
            color: #222;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th, td {
            padding: 16px;
            text-align: center;
            border-bottom: 1px solid #e0e0e0;
        }
        th {
            background-color: #2ecc71;
            color: white;
        }
        tr:last-child td {
            border-bottom: none;
        }
        .qty-btn {
            padding: 6px 12px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            margin: 0 4px;
            font-size: 16px;
        }
        .qty-btn:hover {
            background-color: #2980b9;
        }
        .btn {
            padding: 10px 16px;
            border-radius: 6px;
            color: white;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        .btn.hapus { background-color: #e74c3c; }
        .btn.hapus:hover { background-color: #c0392b; }
        .btn.kosongkan {
            background-color: #e67e22;
            display: inline-block;
            margin-top: 20px;
        }
        .btn.kosongkan:hover { background-color: #d35400; }
        .btn.kembali {
            background-color: #f1c40f;
            color: #333;
            margin-bottom: 20px;
            display: inline-block;
        }
        .btn.kembali:hover { background-color: #d4ac0d; }
        .btn.checkout {
            background-color: #27ae60;
            float: right;
        }
        .btn.checkout:hover { background-color: #1e8449; }
        .total {
            font-size: 22px;
            font-weight: bold;
            text-align: right;
            padding: 10px;
            color: #2c3e50;
        }
        .empty-message {
            text-align: center;
            font-size: 20px;
            color: #777;
        }
        .countdown {
            text-align: right;
            font-size: 16px;
            color: #e74c3c;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<div class="container">
    <a href="../Scanner/scan.php" class="btn kembali">← Kembali ke Belanja</a>
    <h2>Keranjang Belanja</h2>

    <?php if (!empty($_SESSION['keranjang'])): ?>

        <?php
            // Hitung waktu tersisa untuk JavaScript
            $sisa_detik = $waktu_expired - (time() - $_SESSION['keranjang_waktu']);
        ?>

        <div class="countdown">
            ⏳ Waktu checkout: <span id="timer"><?= floor($sisa_detik / 60) ?>m <?= $sisa_detik % 60 ?>d</span>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Nama Produk</th>
                    <th>Harga</th>
                    <th>Qty</th>
                    <th>Subtotal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $total = 0;
                foreach ($_SESSION['keranjang'] as $item): 
                    $subtotal = $item['harga'] * $item['qty'];
                    $total += $subtotal;
                ?>
                    <tr>
                        <td><?= $item['nama'] ?></td>
                        <td>Rp <?= number_format($item['harga'], 0, ',', '.') ?></td>
                        <td>
                            <a href="../service/update-qty.php?id=<?= $item['id_produk'] ?>&action=kurang" class="qty-btn">−</a>
                            <?= $item['qty'] ?>
                            <a href="../service/update-qty.php?id=<?= $item['id_produk'] ?>&action=tambah" class="qty-btn">+</a>
                        </td>
                        <td>Rp <?= number_format($subtotal, 0, ',', '.') ?></td>
                        <td><a href="../service/hapus-keranjang.php?id=<?= $item['id_produk'] ?>" class="btn hapus">Hapus</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="total">Total: Rp <?= number_format($total, 0, ',', '.') ?></div>

        <a href="../service/hapus-semua-keranjang.php" class="btn kosongkan">🗑️ Kosongkan Keranjang</a>
        <a href="checkout.php" class="btn checkout">Checkout</a>
        
    <?php else: ?>
        <p class="empty-message">Keranjang masih kosong 😢</p>
    <?php endif; ?>
</div>

<script>
    // === Countdown Timer JavaScript ===
    let seconds = <?= $sisa_detik ?>;

    function updateTimer() {
        if (seconds <= 0) {
            clearInterval(timer);
            alert("Waktu checkout habis. Keranjang akan dikosongkan.");
            location.reload();
        } else {
            let m = Math.floor(seconds / 60);
            let s = seconds % 60;
            document.getElementById('timer').innerText = `${m}m ${s < 10 ? '0' + s : s}d`;
            seconds--;
        }
    }

    let timer = setInterval(updateTimer, 1000);
</script>

</body>
</html>
