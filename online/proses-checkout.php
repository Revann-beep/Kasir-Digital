<?php
session_start();
include '../service/conection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $total    = (int) $_POST['total'];
    $diskon   = (int) $_POST['diskon'];
    $total_bayar = $total - $diskon;
    $metode_pembayaran = 'Tunai'; // default

    $fid_admin = $_SESSION['admin']['id'] ?? 0;
    $fid_member = $_SESSION['member']['id'] ?? 0;

    // Ambil keranjang
    $keranjang = $_SESSION['keranjang'] ?? [];

    $produkList = [];
    if (!empty($keranjang)) {
        $ids = implode(",", array_keys($keranjang));
        $query = mysqli_query($conn, "SELECT * FROM produk WHERE id_produk IN ($ids)");
        while ($row = mysqli_fetch_assoc($query)) {
            $id = $row['id_produk'];
            $qty = $keranjang[$id];
            $harga = $row['harga_jual'];
            $subtotal = $harga * $qty;

            $produkList[] = [
                'fid_produk' => $id,
                'nama' => $row['nama_produk'],
                'qty' => $qty,
                'harga' => $harga,
                'subtotal' => $subtotal
            ];
        }
    }

    // Gabungkan detail produk ke dalam satu string (untuk kolom `detail`)
    $detail_text = implode(", ", array_map(function ($p) {
        return $p['nama'] . " x" . $p['qty'];
    }, $produkList));

    // Simpan transaksi
    $stmt = $conn->prepare("INSERT INTO transaksi (
        tgl_pembelian, total_harga, fid_admin, fid_member,
        total_keuntungan, detail, uang_dibayar, diskon,
        total_bayar, kembalian, metode_pembayaran
    ) VALUES (NOW(), ?, ?, ?, 0, ?, ?, ?, ?, 0, ?)");

    $uang_dibayar = $total_bayar;

    // Bind param: ada 8 tanda tanya (?), jadi 8 variabel
    // Format: i = integer, s = string
    $stmt->bind_param(
        "iiisisis",
        $total,
        $fid_admin,
        $fid_member,
        $detail_text,
        $uang_dibayar,
        $diskon,
        $total_bayar,
        $metode_pembayaran
    );

    $stmt->execute();
    $id_transaksi = $stmt->insert_id;

    // Simpan detail produk
    foreach ($produkList as $item) {
        $stmtDetail = $conn->prepare("INSERT INTO detail_transaksi (fid_transaksi, fid_produk, qty, harga, subtotal) VALUES (?, ?, ?, ?, ?)");
        $stmtDetail->bind_param("iiiii", $id_transaksi, $item['fid_produk'], $item['qty'], $item['harga'], $item['subtotal']);
        $stmtDetail->execute();
    }

    // Bersihkan keranjang
    unset($_SESSION['keranjang']);
    unset($_SESSION['keranjang_waktu']);

    // Redirect ke invoice
    header("Location: invoice.php?id_transaksi=$id_transaksi");
    exit;
}
?>
