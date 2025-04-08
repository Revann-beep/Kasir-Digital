<?php
include '../service/conection.php'; // Pastikan path ini sesuai

// Ambil data dari form (simulasi)
$tgl_pembelian = date('Y-m-d');
$total_harga = 50000; // Contoh harga
$fid_admin = 1;
$fid_produk = 2;
$detail = "Pembelian produk A";
$fid_member = 0; // ← Kalau ini 0, harus dipastikan ada member dengan id 0
$total_keuntungan = 10000;

// Hitung poin: 1 poin setiap kelipatan 10000
$poin = floor($total_harga / 10000);

// 🔍 DEBUG SEBELUM INSERT
echo "<pre>";
echo "==== DEBUG DATA ====\n";
echo "tgl_pembelian     : $tgl_pembelian\n";
echo "total_harga       : $total_harga\n";
echo "fid_admin         : $fid_admin\n";
echo "fid_produk        : $fid_produk\n";
echo "detail            : $detail\n";
echo "fid_member        : $fid_member\n";
echo "total_keuntungan  : $total_keuntungan\n";
echo "poin dihitung     : $poin\n";
echo "</pre>";

// Simpan transaksi
$insert_transaksi = mysqli_query($conn, "INSERT INTO transaksi (
    tgl_pembelian, total_harga, fid_admin, fid_produk, detail, fid_member, total_keuntungan
) VALUES (
    '$tgl_pembelian', '$total_harga', '$fid_admin', '$fid_produk', '$detail', '$fid_member', '$total_keuntungan'
)");

if ($insert_transaksi) {
    // Update poin member
    $update_poin = mysqli_query($conn, "UPDATE member SET point = point + $poin WHERE id_member = $fid_member");

    echo "Transaksi berhasil dan poin ditambahkan!";
} else {
    echo "❌ Gagal menyimpan transaksi: " . mysqli_error($conn);
}
?>
