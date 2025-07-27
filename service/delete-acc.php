<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['id'])) {
    include '../service/conection.php';

    $id = intval($_POST['id']);

    // Cegah admin yang sedang login menghapus dirinya sendiri
    if (isset($_SESSION['id_admin']) && $_SESSION['id_admin'] == $id) {
        $_SESSION["pesan"] = "Admin yang sedang login tidak dapat dihapus!";
        header("Location: ../admin/admin.php");
        exit;
    }

    // Cek status admin
    $check = $conn->prepare("SELECT id, status FROM admin WHERE id = ?");
    $check->bind_param("i", $id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        $status = strtolower(trim($admin['status'])); // 👈 Perbaikan di sini

        if ($status === 'tidak aktif') {
            $delete = $conn->prepare("DELETE FROM admin WHERE id = ?");
            $delete->bind_param("i", $id);
            if ($delete->execute()) {
                $_SESSION["pesan"] = "Admin berhasil dihapus.";
            } else {
                $_SESSION["pesan"] = "Gagal menghapus admin: " . $delete->error;
            }
            $delete->close();
        } else {
            $_SESSION["pesan"] = "Admin berstatus aktif tidak dapat dihapus!";
        }
    } else {
        $_SESSION["pesan"] = "Admin tidak ditemukan!";
    }

    $check->close();
    $conn->close();

    header("Location: ../admin/admin.php");
    exit;
} else {
    echo "Akses tidak valid!";
}
?>
