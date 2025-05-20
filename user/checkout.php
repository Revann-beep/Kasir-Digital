<?php
session_start();
include '../service/conection.php';

if (!isset($_SESSION['keranjang']) || empty($_SESSION['keranjang'])) {
    die("Keranjang kosong.");
}

$id_admin = $_SESSION['id_admin'] ?? 1;
$fid_member = $_SESSION['fid_member'] ?? 'NULL';

$produk_dipilih = $_POST['produk_dipilih'] ?? [];

if (empty($produk_dipilih)) {
    die("Tidak ada produk yang dipilih untuk checkout.");
}

$total_awal = 0;
$keranjang_checkout = [];

foreach ($produk_dipilih as $id_produk) {
    if (isset($_SESSION['keranjang'][$id_produk])) {
        $item = $_SESSION['keranjang'][$id_produk];
        $qty = (int) $item['qty'];
        $harga = (int) $item['harga'];
        $subtotal = $harga * $qty;
        $total_awal += $subtotal;
        $keranjang_checkout[$id_produk] = $item;
    }
}

// Ambil metode pembayaran & uang dibayar
$metode = $_POST['metode_pembayaran'] ?? 'Tunai';
$uang_dibayar = isset($_POST['uang_dibayar']) ? (int) $_POST['uang_dibayar'] : 0;

// Diskon 10% jika member aktif
$diskon = 0;
$is_member_aktif = false;
if ($fid_member !== 'NULL') {
    $q = mysqli_query($conn, "SELECT poin FROM member WHERE id_member = $fid_member AND status='aktif'");
    if ($q && mysqli_num_rows($q)) {
        $is_member_aktif = true;
        $diskon = floor($total_awal * 0.1); // diskon 10%
    }
}

$total_bayar = $total_awal - $diskon;

// Validasi uang
if ($metode === 'Tunai' && $uang_dibayar < $total_bayar) {
    die("Uang tidak mencukupi. Total bayar: Rp " . number_format($total_bayar));
}

// Jika non-Tunai, anggap dibayar pas
if ($metode !== 'Tunai') {
    $uang_dibayar = $total_bayar;
    $kembalian = 0;
} else {
    $kembalian = $uang_dibayar - $total_bayar;
}

// Simpan transaksi
$sql_transaksi = "
    INSERT INTO transaksi (tgl_pembelian, total_harga, diskon, total_bayar, uang_dibayar, kembalian, metode_pembayaran, fid_admin, fid_member)
    VALUES (NOW(), $total_awal, $diskon, $total_bayar, $uang_dibayar, $kembalian, '$metode', $id_admin, " . ($fid_member === 'NULL' ? "NULL" : $fid_member) . ")
";
mysqli_query($conn, $sql_transaksi);
$id_transaksi = mysqli_insert_id($conn);

// Detail transaksi dan update stok
foreach ($keranjang_checkout as $id_produk => $item) {
    $id_produk = (int) $item['id_produk'];
    $qty = (int) $item['qty'];
    $harga = (int) $item['harga'];
    $subtotal = $harga * $qty;

    mysqli_query($conn, "
        INSERT INTO detail_transaksi (fid_transaksi, fid_produk, qty, harga, subtotal)
        VALUES ($id_transaksi, $id_produk, $qty, $harga, $subtotal)
    ");

    mysqli_query($conn, "UPDATE produk SET stok = stok - $qty WHERE id_produk = $id_produk");

    // Hapus produk dari keranjang setelah sukses
    unset($_SESSION['keranjang'][$id_produk]);
}

// Tambah poin untuk member aktif
if ($fid_member !== 'NULL' && $is_member_aktif) {
    $poin_baru = floor($total_bayar / 1000);
    mysqli_query($conn, "UPDATE member SET poin = poin + $poin_baru WHERE id_member = $fid_member");
}

// Kosongkan keranjang jika sudah tidak ada produk
if (empty($_SESSION['keranjang'])) {
    unset($_SESSION['keranjang']);
    unset($_SESSION['keranjang_waktu']);
}

// Reset session member
unset($_SESSION['fid_member']);

// Simpan data invoice
$_SESSION['invoice'] = [
    'id_transaksi' => $id_transaksi,
    'total' => $total_awal,
    'diskon' => $diskon,
    'grand_total' => $total_bayar,
    'uang_dibayar' => $uang_dibayar,
    'kembalian' => $kembalian,
    'metode' => $metode
];

// Redirect ke invoice
header("Location: invoice.php?id_transaksi=$id_transaksi");
exit;
?>
