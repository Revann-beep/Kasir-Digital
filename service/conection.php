<?php
date_default_timezone_set('Asia/Jakarta'); // Tambahkan ini paling atas

$conn = mysqli_connect("localhost", "root", "", "kasir");
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
