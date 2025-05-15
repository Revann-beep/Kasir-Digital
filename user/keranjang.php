<?php
session_start();
include '../service/conection.php';

// === Set masa aktif keranjang ===
$waktu_expired = 10 * 60; // 10 menit

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

// Ambil member dari sesi jika ada
$fid_member = isset($_SESSION['fid_member']) ? $_SESSION['fid_member'] : '';
$member = null;
$poin_diskon = 0;
$total = 0;

// Cari member berdasarkan no_telp
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['no_telp'])) {
    $no_telp = mysqli_real_escape_string($conn, $_POST['no_telp']);
    $query = mysqli_query($conn, "SELECT * FROM member WHERE no_telp = '$no_telp' AND status = 'aktif'");
    if (mysqli_num_rows($query) > 0) {
        $member = mysqli_fetch_assoc($query);
        $_SESSION['fid_member'] = $member['id_member'];
        header("Location: keranjang.php");
        exit;
    } else {
        echo "<script>alert('Member dengan nomor telepon tersebut tidak ditemukan.');</script>";
    }
}

// Hitung total dan poin
if (!empty($_SESSION['keranjang'])) {
    foreach ($_SESSION['keranjang'] as $item) {
        $subtotal = $item['harga'] * $item['qty'];
        $total += $subtotal;
    }

    if ($fid_member) {
        $q = mysqli_query($conn, "SELECT * FROM member WHERE id_member = $fid_member");
        $member = mysqli_fetch_assoc($q);
        $poin_diskon = $member ? min($member['poin'], floor($total / 100)) * 100 : 0;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Keranjang Belanja</title>
    <style>
        /* (Tetap gaya sama seperti sebelumnya) */
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f5f7fa;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 960px;
            margin: 40px auto;
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        }
        h2 { text-align: center; font-size: 28px; margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 16px; text-align: center; border-bottom: 1px solid #e0e0e0; }
        th { background-color: #2ecc71; color: white; }
        .btn { padding: 10px 16px; border-radius: 6px; text-decoration: none; font-weight: bold; display: inline-block; margin: 5px 0; }
        .btn.kembali { background-color: #f1c40f; color: #333; }
        .btn.checkout { background-color: #27ae60; color: white; float: right; }
        .btn.hapus { background-color: #e74c3c; color: white; }
        .btn.kosongkan { background-color: #e67e22; color: white; }
        .qty-btn { padding: 6px 12px; background-color: #3498db; color: white; border-radius: 6px; text-decoration: none; margin: 0 5px; }
        .total { text-align: right; font-size: 20px; margin-top: 20px; }
        .countdown { text-align: right; color: #e74c3c; margin: 10px 0; }
    </style>
</head>
<body>
<div class="container">
    <a href="../Scanner/scan.php" class="btn kembali">← Kembali ke Belanja</a>
    <h2>Keranjang Belanja</h2>

    <!-- Form Member -->
    <form method="post" action="">
        <label>No. Telepon Member:</label>
        <input type="text" name="no_telp" required placeholder="08xxxxxxxxx">
        <button type="submit" class="btn">Cari Member</button>
    </form>

    <?php if ($member): ?>
        <p><strong>Member:</strong> <?= $member['nama_member'] ?> | <strong>Poin:</strong> <?= number_format($member['poin']) ?></p>
    <?php endif; ?>

    <!-- Hitung waktu tersisa -->
    <?php $sisa_detik = $waktu_expired - (time() - $_SESSION['keranjang_waktu']); ?>
    <div class="countdown">⏳ Sisa waktu checkout: <span id="timer"><?= floor($sisa_detik / 60) ?>m <?= $sisa_detik % 60 ?>d</span></div>

    <?php if (!empty($_SESSION['keranjang'])): ?>
        <table>
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Harga</th>
                    <th>Qty</th>
                    <th>Subtotal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($_SESSION['keranjang'] as $item): 
                $subtotal = $item['harga'] * $item['qty']; ?>
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

        <!-- Total -->
        <div class="total">
            Total: Rp <?= number_format($total, 0, ',', '.') ?><br>
            <?php if ($poin_diskon > 0): ?>
                Diskon Poin: Rp <?= number_format($poin_diskon, 0, ',', '.') ?><br>
                <strong>Grand Total: Rp <?= number_format($total - $poin_diskon, 0, ',', '.') ?></strong>
            <?php endif; ?>
        </div>

        <!-- Form Checkout -->
        <form method="post" action="checkout.php" onsubmit="return validateBayar()">
    <label><strong>Pilih Metode Pembayaran:</strong></label><br>
    <input type="radio" name="metode_pembayaran" value="Tunai" required> Tunai<br>
    <input type="radio" name="metode_pembayaran" value="QRIS"> QRIS<br><br>

    <label><strong>Uang Dibayar:</strong></label><br>
    <input type="number" name="uang_dibayar" id="uang_dibayar" required><br><br>

    <input type="hidden" name="total" value="<?= $total ?>">
    <input type="hidden" name="diskon" value="<?= $poin_diskon ?>">
    <input type="hidden" name="grand_total" value="<?= $total - $poin_diskon ?>">

    <button type="submit" class="btn checkout">Checkout</button>
</form>

<script>
function validateBayar() {
    let bayar = parseInt(document.getElementById('uang_dibayar').value);
    let total = <?= $total - $poin_diskon ?>;
    if (bayar < total) {
        alert("Uang dibayar kurang dari total belanja!");
        return false;
    }
    return true;
}
</script>


        <a href="../service/hapus-semua-keranjang.php" class="btn kosongkan">🗑 Kosongkan</a>

    <?php else: ?>
        <p style="text-align:center;">Keranjang masih kosong 😢</p>
    <?php endif; ?>
</div>

<script>
    let seconds = <?= $sisa_detik ?>;

    function updateTimer() {
        if (seconds <= 0) {
            clearInterval(timer);
            if (!localStorage.getItem('keranjangExpired')) {
                localStorage.setItem('keranjangExpired', 'true');
                alert("Waktu checkout habis. Halaman akan dimuat ulang.");
                location.reload();
            }
        } else {
            let m = Math.floor(seconds / 60);
            let s = seconds % 60;
            document.getElementById('timer').innerText = `${m}m ${s < 10 ? '0' + s : s}d`;
            seconds--;
        }
    }

    // Bersihkan localStorage setelah reload (biar bisa alert lagi kalau keranjang baru)
    window.onload = function() {
        if (localStorage.getItem('keranjangExpired')) {
            localStorage.removeItem('keranjangExpired');
        }
    }

    let timer = setInterval(updateTimer, 1000);
</script>

</body>
</html>
