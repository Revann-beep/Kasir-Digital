<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    include '../service/conection.php'; // Pastikan koneksi benar

    $id = intval($_POST['id']); // Pastikan ID adalah angka

    // Cek apakah ID ada sebelum menghapus
    $check = $conn->prepare("SELECT id FROM admin WHERE id = ?");
    $check->bind_param("i", $id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) { // Jika ID ditemukan
        $sql = "DELETE FROM admin WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            header("Location: ../admin/admin.php");
            exit;
        } else {
            echo "Error: " . $stmt->error;
        }
    } else {
        echo "ID tidak ditemukan di database!";
    }

    $check->close();
    $stmt->close();
    $conn->close();
} else {
    echo "Akses tidak valid!";
}
?>
