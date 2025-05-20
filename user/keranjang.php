<?php
session_start();
include '../service/conection.php';

// Set waktu expired keranjang (1 jam)
$waktu_expired = 60 * 60;
if (isset($_SESSION['keranjang'])) {
    if (!isset($_SESSION['keranjang_waktu'])) {
        $_SESSION['keranjang_waktu'] = time();
    } elseif (time() - $_SESSION['keranjang_waktu'] > $waktu_expired) {
        unset($_SESSION['keranjang']);
        unset($_SESSION['keranjang_waktu']);
        unset($_SESSION['fid_member']);
        echo "<script>alert('Waktu checkout habis (1 jam), keranjang dihapus.'); location.reload();</script>";
        exit;
    }
}

// Reset member jika tombol reset ditekan
if (isset($_POST['reset_member'])) {
    unset($_SESSION['fid_member']);
    header("Location: keranjang.php");
    exit;
}

$fid_member = $_SESSION['fid_member'] ?? null;
$member = null;
$total = 0;
$poin_diskon = 0;
$error_msg = null;

// Cari member berdasarkan no_telp
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['no_telp']) && !isset($_POST['reset_member'])) {
    $no_telp = mysqli_real_escape_string($conn, $_POST['no_telp']);
    $q = mysqli_query($conn, "SELECT * FROM member WHERE no_telp='$no_telp' AND status='aktif'");
    if (mysqli_num_rows($q) > 0) {
        $member = mysqli_fetch_assoc($q);
        $_SESSION['fid_member'] = $member['id_member'];
        header("Location: keranjang.php");
        exit;
    } else {
        $error_msg = "Member tidak ditemukan atau tidak aktif.";
    }
}

if ($fid_member) {
    $q = mysqli_query($conn, "SELECT * FROM member WHERE id_member=$fid_member");
    $member = mysqli_fetch_assoc($q);
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<title>Keranjang Belanja</title>
<style>
    /* ...style CSS seperti sebelumnya (boleh pakai dari versi kamu)... */
    
        body {
        font-family: Arial, sans-serif;
        background-color: #f7f9fc;
        margin: 0; padding: 20px;
        color: #333;
    }
    .container {
        max-width: 800px;
        background: #fff;
        margin: auto;
        padding: 20px 30px;
        border-radius: 10px;
        box-shadow: 0 3px 12px rgba(0,0,0,0.1);
    }
    h1 {
        text-align: center;
        color: #0077cc;
    }
    form.member-form {
        margin-bottom: 20px;
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: center;
    }
    form.member-form input[type="text"] {
        padding: 8px;
        font-size: 16px;
        border: 1.5px solid #ccc;
        border-radius: 6px;
        width: 220px;
    }
    form.member-form button {
        background-color: #0077cc;
        color: white;
        border: none;
        padding: 9px 18px;
        font-weight: bold;
        cursor: pointer;
        border-radius: 6px;
        transition: background-color 0.3s ease;
    }
    form.member-form button:hover {
        background-color: #005fa3;
    }
    .reset-member {
        background-color: #cc3300;
    }
    .reset-member:hover {
        background-color: #992400;
    }
    .error-msg {
        color: #cc3300;
        text-align: center;
        margin-bottom: 15px;
    }
    .member-info {
        text-align: center;
        margin-bottom: 15px;
        font-weight: bold;
        color: #006633;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 25px;
    }
    th, td {
        padding: 12px 10px;
        border-bottom: 1px solid #ddd;
        text-align: center;
    }
    th {
        background-color: #0077cc;
        color: white;
    }
    td:first-child {
        text-align: left;
    }
    a.qty-btn, a.hapus-btn {
        padding: 5px 10px;
        border-radius: 6px;
        color: white;
        text-decoration: none;
        font-weight: bold;
        margin: 0 3px;
    }
    a.qty-btn {
        background-color: #0077cc;
    }
    a.qty-btn:hover {
        background-color: #005fa3;
    }
    a.hapus-btn {
        background-color: #cc3300;
    }
    a.hapus-btn:hover {
        background-color: #992400;
    }
    .total, .discount, .final-total {
        text-align: right;
        font-size: 18px;
        margin-bottom: 5px;
    }
    .checkout-btn, .empty-btn {
        display: inline-block;
        padding: 12px 25px;
        border-radius: 8px;
        font-weight: bold;
        cursor: pointer;
        border: none;
        transition: background-color 0.3s ease;
        margin-top: 10px;
    }
    .checkout-btn {
        background-color: #009933;
        color: white;
        float: right;
    }
    .checkout-btn:hover {
        background-color: #007f29;
    }
    .empty-btn {
        background-color: #cc3300;
        color: white;
    }
    .empty-btn:hover {
        background-color: #992400;
    }
    .clearfix::after {
        content: "";
        display: table;
        clear: both;
    }
    label {
        font-weight: bold;
    }
    input[type="number"] {
        width: 100%;
        max-width: 250px;
        padding: 8px;
        margin: 5px 0 20px 0;
        font-size: 16px;
        border: 1.5px solid #ccc;
        border-radius: 6px;
    }
    .payment-methods {
        margin-bottom: 20px;
    }
    .payment-methods label {
        margin-right: 20px;
        font-weight: normal;
        cursor: pointer;
    
    }

    input[type="checkbox"] {
        transform: scale(1.2);
        cursor: pointer;
    }
    .total-box {
        font-size: 18px;
        text-align: right;
        margin-top: 10px;
    }
</style>
<script>
function hitungTotal() {
    let checkboxes = document.querySelectorAll('input[name="produk_dipilih[]"]:checked');
    let total = 0;
    checkboxes.forEach(cb => {
        total += parseInt(cb.dataset.subtotal);
    });

    const diskon = <?= $member ? min($member['poin'], floor(999999999 / 100)) * 100 : 0 ?>;
    const diskonAktif = Math.min(diskon, Math.floor(total / 100) * 100);

    document.getElementById('total-harga').textContent = 'Rp ' + total.toLocaleString();
    document.getElementById('diskon-poin').textContent = 'Rp ' + diskonAktif.toLocaleString();
    document.getElementById('total-bayar').textContent = 'Rp ' + (total - diskonAktif).toLocaleString();

    document.getElementById('total-section').style.display = checkboxes.length ? 'block' : 'none';
}
</script>
</head>
<body onload="hitungTotal()">
<div class="container">
    <h1>Keranjang Belanja</h1>

    <?php if ($error_msg): ?>
        <p class="error-msg"><?= $error_msg ?></p>
    <?php endif; ?>

    <form method="post" class="member-form">
        <input type="text" name="no_telp" placeholder="Masukkan No. Telepon Member" required value="<?= htmlspecialchars($member['no_telp'] ?? '') ?>">
        <button type="submit">Cari Member</button>
        <?php if ($fid_member): ?>
            <button type="submit" name="reset_member" class="reset-member">Reset Member</button>
        <?php endif; ?>
    </form>

    <?php if ($member): ?>
        <p class="member-info">Member: <?= htmlspecialchars($member['nama_member']) ?> | Poin: <?= number_format($member['poin']) ?></p>
    <?php endif; ?>

    <?php if (!empty($_SESSION['keranjang'])): ?>
    <form method="post" action="../user/checkout.php" onsubmit="return confirm('Yakin melakukan checkout?')">
        <table>
            <thead>
                <tr>
                    <th></th>
                    <th>Produk</th>
                    <th>Harga (Rp)</th>
                    <th>Qty</th>
                    <th>Subtotal (Rp)</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($_SESSION['keranjang'] as $item): 
                $subtotal = $item['harga'] * $item['qty']; ?>
                <tr>
                    <td><input type="checkbox" name="produk_dipilih[]" value="<?= $item['id_produk'] ?>" data-subtotal="<?= $subtotal ?>" onchange="hitungTotal()" /></td>
                    <td><?= htmlspecialchars($item['nama']) ?></td>
                    <td><?= number_format($item['harga']) ?></td>
                    <td>
                        <a href="../service/update-qty.php?id=<?= $item['id_produk'] ?>&action=kurang" class="qty-btn">−</a>
                        <?= $item['qty'] ?>
                        <a href="../service/update-qty.php?id=<?= $item['id_produk'] ?>&action=lebih" class="qty-btn">+</a>
                    </td>
                    <td><?= number_format($subtotal) ?></td>
                    <td><a href="../service/hapus-keranjang.php?id=<?= $item['id_produk'] ?>" class="hapus-btn">Hapus</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <div id="total-section" style="display: none;">
            <div class="total-box">Total: <span id="total-harga">Rp 0</span></div>
            <div class="total-box">Diskon Poin: <span id="diskon-poin">Rp 0</span></div>
            <div class="total-box"><strong>Total Bayar: <span id="total-bayar">Rp 0</span></strong></div>
        </div>

        <label for="uang_dibayar">Uang Dibayar (Rp):</label>
        <input type="number" name="uang_dibayar" min="0" required>

        <div class="payment-methods">
            <label><input type="radio" name="metode_pembayaran" value="tunai" checked> Tunai</label>
            <label><input type="radio" name="metode_pembayaran" value="gopay"> GoPay</label>
            <label><input type="radio" name="metode_pembayaran" value="ovo"> OVO</label>
            <label><input type="radio" name="metode_pembayaran" value="dana"> Dana</label>
        </div>

        <button type="submit" class="checkout-btn">Checkout</button>
    </form>

    <form method="post" action="../service/hapus-keranjang.php" onsubmit="return confirm('Kosongkan keranjang?')">
        <button type="submit" class="empty-btn">Kosongkan Keranjang</button>
    </form>

    <?php else: ?>
        <p style="text-align:center; font-style: italic;">Keranjang kosong.</p>
    <?php endif; ?>
</div>
</body>
</html>
