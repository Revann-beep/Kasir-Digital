<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "kasir";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = intval($_POST['id']);
    $status = $_POST['status'] == 'Aktif' ? 'Aktif' : 'Tidak Aktif'; // Validasi

    $stmt = $conn->prepare("UPDATE admin SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
}

$conn->close();
header("Location: ../admin.php"); // Ganti sesuai nama file utama
exit;
