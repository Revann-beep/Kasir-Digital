<?php
include '../service/conection.php'; // Koneksi ke database

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password']; // Langsung menyimpan password tanpa hash (TIDAK AMAN)

    $target_dir = "../assets/";

    // Pastikan folder assets ada dan bisa ditulis
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    // Cek apakah ada file gambar yang diunggah
    if (!empty($_FILES['gambar']['name'])) {
        $file_name = basename($_FILES["gambar"]["name"]);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($file_ext, $allowed_types)) {
            $new_file_name = time() . "_" . $file_name; // Tambah timestamp agar unik
            $target_file = $target_dir . $new_file_name;

            if (move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file)) {
                $gambar = $new_file_name; // <-- ini sudah benar

            } else {
                $gambar = "user-icon.png"; // Default jika upload gagal
            }
        } else {
            $gambar = "user-icon.png"; // Default jika format tidak valid
        }
    } else {
        $gambar = "user-icon.png"; // Default jika tidak ada gambar
    }

    // Query insert ke database (tanpa hash)
    $sql = "INSERT INTO admin (email, username, password, gambar) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $email, $username, $password, $gambar);

    if ($stmt->execute()) {
        header("Location: ../admin/admin.php"); // Redirect ke halaman admin
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Admin</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f4f4f4;
            font-family: Arial, sans-serif;
        }
        .form-container {
            background: #9ec3af;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
            text-align: center;
        }
        h2 {
            text-align: center;
            font-weight: bold;
        }
        label {
            font-weight: bold;
            display: block;
            margin-top: 10px;
            text-align: left;
        }
        input {
            width: 90%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background: blue;
            color: white;
        }
        input[type="file"] {
            background: none;
            color: black;
        }
        .btn {
            background: red;
            color: white;
            padding: 10px;
            border: none;
            cursor: pointer;
            width: 100%;
            margin-top: 15px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 5px;
        }
        .btn:hover {
            background: darkred;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Tambah Admin</h2>
    <form action="add-acc.php" method="POST" enctype="multipart/form-data">
        <label>Email:</label>
        <input type="email" name="email" required>

        <label>Username:</label>
        <input type="text" name="username" required>

        <label>Image:</label>
        <input type="file" name="gambar">

        <label>Password:</label>
        <input type="password" name="password" required>

        <button type="submit" class="btn">Tambah Admin</button>
    </form>
</div>

</body>
</html>