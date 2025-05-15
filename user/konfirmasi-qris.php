<?php
session_start();
include '../service/conection.php';

// Cek kalau metode pembayaran QRIS dan keranjang ada
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['metode_pembayaran']) && $_POST['metode_pembayaran'] === 'QRIS' && !empty($_SESSION['keranjang'])) {

    $total = isset($_POST['total']) ? (int) $_POST['total'] : 0;
    $id_admin = $_SESSION['id_admin'] ?? 1;
    $fid_member = $_SESSION['fid_member'] ?? 'NULL';

    // Hitung total dari session untuk validasi
    $total_keranjang = 0;
    foreach ($_SESSION['keranjang'] as $item) {
        $total_keranjang += $item['harga'] * $item['qty'];
    }

    if ($total !== $total_keranjang) {
        die("Data total tidak valid.");
    }

    // Simpan transaksi ke DB
    mysqli_query($conn, "
        INSERT INTO transaksi (tgl_pembelian, total_harga, diskon, total_bayar, uang_dibayar, kembalian, metode_pembayaran, fid_admin, fid_member)
        VALUES (NOW(), $total_keranjang, 0, $total_keranjang, 0, 0, 'QRIS', $id_admin, " . ($fid_member === 'NULL' ? "NULL" : $fid_member) . ")
    ");
    $id_transaksi = mysqli_insert_id($conn);

    // Simpan detail transaksi
    foreach ($_SESSION['keranjang'] as $item) {
        $id_produk = (int) $item['id_produk'];
        $qty = (int) $item['qty'];
        $harga = (int) $item['harga'];
        $subtotal = $harga * $qty;

        mysqli_query($conn, "
            INSERT INTO detail_transaksi (fid_transaksi, fid_produk, qty, harga, subtotal)
            VALUES ($id_transaksi, $id_produk, $qty, $harga, $subtotal)
        ");
    }

    // Hapus keranjang dan member dari session
    unset($_SESSION['keranjang']);
    unset($_SESSION['keranjang_waktu']);
    unset($_SESSION['fid_member']);

    echo "<h2>Pembayaran QRIS berhasil!</h2>";
    echo "<p>Total bayar: Rp " . number_format($total, 0, ',', '.') . "</p>";
    echo "<a href='../Scanner/scan.php'>Kembali ke Belanja</a>";

} else {
    echo "Tidak ada data pembayaran atau keranjang kosong.";
}
?>
