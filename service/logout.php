<?php
session_start();

if (isset($_SESSION['admin_id'])) {
    $adminId = $_SESSION['admin_id'];
    $conn = new mysqli("localhost", "root", "", "kasir");

    if (!$conn->connect_error) {
        $conn->query("UPDATE admin SET status = 'Nonaktif' WHERE id = $adminId");
        $conn->close();
    }
}

session_destroy();
header("Location: ../admin/index.php");
exit;
