<?php
session_start();
include '../service/conection.php';

if (!isset($_SESSION['keranjang']) || empty($_SESSION['keranjang'])) {
    die("Keranjang kosong.");
}

$id_admin = $_SESSION['id_admin'] ?? 1; // Simulasi admin sementara
$fid_member = $_SESSION['fid_member'] ?? 'NULL';

// Hitung total awal dari keranjang
$total_awal = 0;
foreach ($_SESSION['keranjang'] as $id_produk => $item) {
    $qty = (int) $item['qty'];
    $harga = (int) $item['harga'];
    $subtotal = $harga * $qty;
    $total_awal += $subtotal;
}

// Ambil metode pembayaran, default Tunai
$metode = $_POST['metode_pembayaran'] ?? 'Tunai';

// Ambil uang dibayar, default 0
$uang_dibayar = isset($_POST['uang_dibayar']) ? (int) $_POST['uang_dibayar'] : 0;

// Hitung diskon dari poin member
$diskon = 0;
if ($fid_member !== 'NULL') {
    $q_poin = mysqli_query($conn, "SELECT poin FROM member WHERE id_member = $fid_member");
    if ($q_poin && mysqli_num_rows($q_poin)) {
        $data_poin = mysqli_fetch_assoc($q_poin);
        $maks_diskon = $data_poin['poin'] * 100; // 1 poin = 100 rupiah
        $diskon = min($maks_diskon, $total_awal);
        $poin_dipakai = floor($diskon / 100);
    }
}
$total_bayar = $total_awal - $diskon;

// Validasi uang dibayar hanya untuk metode Tunai
if ($metode === 'Tunai' && $uang_dibayar < $total_bayar) {
    die("Uang tidak mencukupi untuk melakukan pembayaran.");
}

// Jika metode QRIS, anggap uang dibayar = total bayar, kembalian 0
if ($metode === 'QRIS') {
    $uang_dibayar = $total_bayar;
    $kembalian = 0;
} else {
    $kembalian = $uang_dibayar - $total_bayar;
}

// Simpan transaksi ke database
mysqli_query($conn, "
    INSERT INTO transaksi (tgl_pembelian, total_harga, diskon, total_bayar, uang_dibayar, kembalian, metode_pembayaran, fid_admin, fid_member)
    VALUES (NOW(), $total_awal, $diskon, $total_bayar, $uang_dibayar, $kembalian, '$metode', $id_admin, " . ($fid_member === 'NULL' ? "NULL" : $fid_member) . ")
");
$id_transaksi = mysqli_insert_id($conn);

// Simpan detail transaksi dan kurangi stok
foreach ($_SESSION['keranjang'] as $id_produk => $item) {
    $id_produk = (int) $item['id_produk'];
    $qty = (int) $item['qty'];
    $harga = (int) $item['harga'];
    $subtotal = $harga * $qty;

    // Simpan ke detail_transaksi
    mysqli_query($conn, "
        INSERT INTO detail_transaksi (fid_transaksi, fid_produk, qty, harga, subtotal)
        VALUES ($id_transaksi, $id_produk, $qty, $harga, $subtotal)
    ");

    // Kurangi stok produk
    mysqli_query($conn, "
        UPDATE produk SET stok = stok - $qty WHERE id_produk = $id_produk
    ");
}

// Kurangi poin yang dipakai jika ada diskon dan member
if ($fid_member !== 'NULL' && $diskon > 0) {
    mysqli_query($conn, "UPDATE member SET poin = poin - $poin_dipakai WHERE id_member = $fid_member");
}

// Tambah poin baru dari total bayar (jika member)
if ($fid_member !== 'NULL') {
    $poin_baru = floor($total_bayar / 1000);
    mysqli_query($conn, "UPDATE member SET poin = poin + $poin_baru WHERE id_member = $fid_member");
}

// Kosongkan keranjang
unset($_SESSION['keranjang']);
unset($_SESSION['keranjang_waktu']);

// Simpan data invoice di session
$_SESSION['invoice'] = [
    'id_transaksi' => $id_transaksi,
    'total' => $total_awal,
    'diskon' => $diskon,
    'grand_total' => $total_bayar,
    'uang_dibayar' => $uang_dibayar,
    'kembalian' => $kembalian,
    'metode' => $metode
];

// Redirect ke halaman invoice
header("Location: invoice.php?id_transaksi=$id_transaksi");
exit;
?>
