<?php
session_start();    
if (isset($_SESSION['admin_id'])) {
    $id = $_SESSION['admin_id'];
    $conn = new mysqli("localhost", "root", "", "kasir");
    $conn->query("UPDATE admin SET status = 'Tidak Aktif' WHERE id = $id");
}
session_destroy();
header("Location: ../admin/index.php"); // kembali ke halaman login
exit;
