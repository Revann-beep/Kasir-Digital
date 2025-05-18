<?php
include '../service/conection.php';

$id = $_GET['id'];

// Cek apakah produk masih digunakan dalam transaksi
$cek = mysqli_query($conn, "SELECT COUNT(*) as total FROM detail_transaksi WHERE fid_produk = '$id'");
$data = mysqli_fetch_assoc($cek);

if ($data['total'] > 0) {
    // Produk masih digunakan, batalkan penghapusan
    echo "<script>
        alert('Produk tidak dapat dihapus karena masih digunakan dalam transaksi.');
        window.location.href='../admin/produk.php';
    </script>";
} else {
    // Produk aman untuk dihapus
    mysqli_query($conn, "DELETE FROM produk WHERE id_produk = '$id'");
    echo "<script>
        alert('Produk berhasil dihapus.');
        window.location.href='../admin/produk.php';
    </script>";
}
?>
