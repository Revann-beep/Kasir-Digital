<?php
include '../service/conection.php';

$id = (int) $_GET['id'];

// Cek apakah produk masih digunakan dalam transaksi
$cekTransaksi = mysqli_query($conn, "SELECT COUNT(*) as total FROM detail_transaksi WHERE fid_produk = '$id'");
$cekTransaksiResult = mysqli_fetch_assoc($cekTransaksi);

// Cek stok produk
$cekStok = mysqli_query($conn, "SELECT stok FROM produk WHERE id_produk = '$id'");
$produk = mysqli_fetch_assoc($cekStok);

// Cek apakah produk masih ada
if (!$produk) {
    echo "<script>
        alert('❌ Produk tidak ditemukan.');
        window.location.href='../admin/produk.php';
    </script>";
    exit;
}

// Validasi: produk masih digunakan
if ($cekTransaksiResult['total'] > 0) {
    echo "<script>
        alert('❌ Produk tidak dapat dihapus karena masih digunakan dalam transaksi.');
        window.location.href='../admin/produk.php';
    </script>";
    exit;
}

// Validasi: stok harus 0
if ($produk['stok'] > 0) {
    echo "<script>
        alert('❌ Produk tidak dapat dihapus karena stok masih tersedia.');
        window.location.href='../admin/produk.php';
    </script>";
    exit;
}

// Hapus dari keranjang dulu
mysqli_query($conn, "DELETE FROM keranjang WHERE id_produk = '$id'");


// Hapus dari tabel produk
mysqli_query($conn, "DELETE FROM produk WHERE id_produk = '$id'");

echo "<script>
    alert('✅ Produk berhasil dihapus.');
    window.location.href='../admin/produk.php';
</script>";
?>
